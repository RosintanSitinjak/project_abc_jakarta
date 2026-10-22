<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\ManagesAttachments;
use Carbon\Carbon;

class OrderController extends Controller
{
    use ManagesAttachments; 

    /**
     * TAMPILKAN DAFTAR PESANAN DENGAN FILTER FLEKSIBEL
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['customer' => fn($q) => $q->withTrashed(), 'items.book', 'thumbnail']);
        
        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Filter Metode & Status Pembayaran
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('shipping_status')) $query->where('shipping_status', $request->shipping_status);
        
        // Filter Tipe Pelanggan (Jemaat, PL, Gereja, Sekolah)
        if ($request->filled('type')) {
            $query->whereHas('customer', fn($q) => $q->where('type', $request->type));
        }

        // Pencarian Kata Kunci (Invoice / Nama Pelanggan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'ILIKE', "%{$search}%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        $orders = $query->latest()->get();

        $orders->transform(function ($order) {
            $order->payment_proof = $order->thumbnail ? asset('storage/' . $order->thumbnail->path) : null;
            return $order;
        });

        return response()->json($orders);
    }

    /**
     * BUAT PESANAN (MENDUKUNG ASSISTED ORDER ADMIN & CHECKOUT USER)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'payment_method' => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.book_id'=> 'required|exists:books,id',
            'items.*.qty'    => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $customer = Customer::findOrFail($request->customer_id);

            // 1. SINKRONKAN NOMOR WHATSAPP KE PROFIL CUSTOMER
            if ($request->filled('customer_phone') || $request->filled('phone')) {
                $phoneInput = $request->input('customer_phone') ?? $request->input('phone');
                if (!empty($phoneInput)) {
                    $customer->update(['phone' => $phoneInput]);
                }
            }

            // 2. Ambil Alamat Pengiriman
            $shippingAddress = $request->input('shipping_address') 
                ?? $request->input('address') 
                ?? $request->input('alamat');

            if ($request->filled('city') || $request->filled('kota')) {
                $city = $request->input('city') ?? $request->input('kota');
                $postal = $request->input('postal_code') ?? $request->input('kode_pos');
                $shippingAddress = trim(($shippingAddress ?? '') . ', ' . $city . ' ' . ($postal ?? ''));
            }

            // 3. DETEKSI METODE PENGIRIMAN
            $rawMethod = strtolower((string) (
                $request->input('delivery_method') 
                ?? $request->input('shipping_method') 
                ?? $request->input('delivery_type') 
                ?? $request->input('shipping_type') 
                ?? ''
            ));

            $hasDeliveryKeyword = in_array($rawMethod, ['delivery', 'ekspedisi', 'kurir', 'shipping']);
            $hasPickupKeyword = in_array($rawMethod, ['pickup', 'ambil_sendiri', 'self_pickup']);

            if ($hasDeliveryKeyword || (!$hasPickupKeyword && !empty($shippingAddress) && strlen(trim($shippingAddress)) > 2)) {
                $deliveryMethod = 'delivery';
            } else {
                $deliveryMethod = 'pickup';
            }

            // 4. DETEKSI SKEMA ONGKOS KIRIM
            $rawPaymentType = strtolower((string) (
                $request->input('shipping_payment_type') 
                ?? $request->input('ongkir_type') 
                ?? ''
            ));

            if ($deliveryMethod === 'pickup') {
                $shippingPaymentType = 'none';
                $shippingFee = 0;
            } elseif (in_array($rawPaymentType, ['cod', 'bayar_ditempat'])) {
                $shippingPaymentType = 'cod';
                $shippingFee = 0;
            } else {
                $shippingPaymentType = 'prepaid';
                $shippingFee = (float) $request->input('shipping_fee', 0);
            }

            // 5. Hitung Subtotal Buku Berdasarkan Peran Pelanggan (PL Mendapat Harga Khusus PL)
            $bookSubtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $book = Book::lockForUpdate()->findOrFail($item['book_id']);

                if ($book->stock < $item['qty']) {
                    abort(422, "Stok buku '{$book->title}' tidak mencukupi (Sisa stok: {$book->stock}).");
                }

                $finalPrice = ($customer->type === 'penginjil') 
                    ? ($book->member_price ?? $book->price) 
                    : $book->price;

                $lineSubtotal = $finalPrice * $item['qty'];
                $bookSubtotal += $lineSubtotal;

                $itemsData[] = [
                    'book'        => $book,
                    'qty'         => $item['qty'],
                    'final_price' => $finalPrice,
                ];
            }

            // 6. Tentukan Status Awal Transaksi & Validasi Aturan Bisnis
            $isWaitingShippingFee = ($deliveryMethod === 'delivery' && $shippingPaymentType === 'prepaid' && $shippingFee <= 0);
            $totalAmount = $bookSubtotal + $shippingFee;
            $paymentMethod = strtolower($request->payment_method) === 'credit' ? 'kredit' : strtolower($request->payment_method);

            // ATURAN 1: Jemaat dilarang memilih opsi kredit
            if ($customer->type === 'jemaat' && $paymentMethod === 'kredit') {
                abort(422, "Pelanggan kategori Jemaat Umum wajib melunasi transaksi secara langsung (Cash, Transfer, atau QRIS).");
            }

            $paidAtStart = (float) $request->input('paid_amount', 0);

            // Untuk pesanan assisted order admin jika tunai/lunas
            if ($customer->type === 'jemaat' && !$isWaitingShippingFee && $paymentMethod !== 'kredit') {
                if ($paidAtStart < $totalAmount && $request->input('source') === 'admin_manual') {
                    $paidAtStart = $totalAmount;
                }
            }

            $remaining = max(0, $totalAmount - $paidAtStart);

            if ($isWaitingShippingFee) {
                $paymentStatus = 'waiting_shipping_fee';
            } else {
                $paymentStatus = ($remaining <= 0) ? 'paid' : 'unpaid';
            }

            // ATURAN 2: Validasi Limit Kredit PL (Maksimal Rp 5.000.000)
            // HANYA dicek jika metode pembayaran yang dipilih adalah 'KREDIT'
            if ($customer->type === 'penginjil' && $paymentMethod === 'kredit') {
                $projectedDebt = (float)$customer->current_debt + $remaining;
                if ($projectedDebt > 5000000) {
                    abort(422, "Akumulasi piutang melebihi limit Penginjil Literatur (Maks Rp 5.000.000). Total hutang berjalan Anda: Rp " . number_format($customer->current_debt, 0, ',', '.') . ". Harap lunasi piutang sebelumnya atau pilih metode pembayaran lunas (Transfer/QRIS/Tunai).");
                }
            }

            // 7. Buat Record Pesanan
            $order = Order::create([
                'customer_id'           => $customer->id,
                'order_number'          => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount'          => $totalAmount,
                'remaining_amount'      => $remaining,
                'shipping_fee'          => $shippingFee,
                'delivery_method'       => $deliveryMethod,
                'shipping_payment_type' => $shippingPaymentType,
                'shipping_address'      => $shippingAddress ?: ($deliveryMethod === 'pickup' ? null : $customer->address),
                'notes'                 => $request->input('notes'),
                'payment_method'        => $paymentMethod,
                'payment_status'        => $paymentStatus,
                'shipping_status'       => 'pending',
                'payment_proof_id'      => $request->payment_proof_id,
                'source'                => $request->input('source', 'website'),
                'created_at'            => $request->date ?? now(),
                'due_date'              => Carbon::parse($request->date ?? now())->addDays(30),
            ]);

            // Simpan foto bukti pembayaran jika ada
            if ($request->filled('payment_proof_id')) {
                $this->setSingleAttachment($order, 'thumbnail', $request->payment_proof_id, 'payment_proof');
            }

            // 8. Simpan Items & Kurangi Stok Buku (ROP)
            foreach ($itemsData as $data) {
                $order->items()->create([
                    'book_id'           => $data['book']->id,
                    'qty'               => $data['qty'],
                    'price_at_purchase' => $data['final_price']
                ]);
                $data['book']->decrement('stock', $data['qty']);
            }

            // 9. Akumulasi Saldo Piutang Customer HANYA untuk metode KREDIT
            if ($paymentMethod === 'kredit' && $remaining > 0 && !$isWaitingShippingFee) {
                $customer->increment('current_debt', $remaining);
            }

            // Catat pembayaran uang muka (DP) jika ada
            if ($paidAtStart > 0) {
                DB::table('debt_payments')->insert([
                    'id'             => Str::uuid(),
                    'customer_id'    => $customer->id,
                    'amount'         => $paidAtStart,
                    'payment_method' => $paymentMethod,
                    'attachment_id'  => $request->payment_proof_id ?? null,
                    'created_at'     => $order->created_at,
                    'updated_at'     => now(),
                ]);
            }

            return response()->json($order->load(['customer', 'items.book', 'thumbnail']), 201);
        });
    }

    /**
     * UPDATE STATUS PESANAN, ONGKIR, LOGISTIK, & CATATAN
     */
    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::with('items.book')->findOrFail($id);
        $oldPaymentStatus = $order->payment_status;
        $customer = Customer::withTrashed()->find($order->customer_id);

        return DB::transaction(function () use ($order, $request, $oldPaymentStatus, $customer) {
            $bookSubtotal = $order->items->sum(function ($item) {
                return (float)$item->price_at_purchase * (int)$item->qty;
            });

            // 1. Penyesuaian Biaya Ongkir oleh Admin
            if ($request->has('shipping_fee')) {
                $newShippingFee = (float) $request->shipping_fee;
                $order->shipping_fee = $newShippingFee;

                $newTotal = $bookSubtotal + $newShippingFee;
                $order->total_amount = $newTotal;

                if ($order->payment_status === 'waiting_shipping_fee') {
                    $order->payment_status = 'unpaid';
                    $order->remaining_amount = $newTotal;
                    
                    // Tambahkan ke utang hanya jika transaksinya adalah KREDIT
                    if ($customer && $order->payment_method === 'kredit') {
                        $customer->increment('current_debt', $newTotal);
                    }
                } else {
                    $alreadyPaid = max(0, (float)$order->total_amount - (float)$order->remaining_amount);
                    $newRemaining = max(0, $newTotal - $alreadyPaid);
                    $diff = $newRemaining - (float)$order->remaining_amount;

                    $order->remaining_amount = $newRemaining;
                    if ($diff != 0 && $customer && $order->payment_status !== 'paid' && $order->payment_method === 'kredit') {
                        if ($diff > 0) {
                            $customer->increment('current_debt', $diff);
                        } else {
                            $customer->decrement('current_debt', abs($diff));
                        }
                    }
                }
            }

            // 2. Pembaruan Status Pembayaran Manual
            if ($request->filled('payment_status')) {
                $newStatus = $request->payment_status;
                if ($oldPaymentStatus !== 'paid' && $newStatus === 'paid') {
                    // Potong current_debt hanya jika transaksi ini adalah transaksi kredit
                    if ($customer && $order->remaining_amount > 0 && $order->payment_method === 'kredit') {
                        $customer->decrement('current_debt', $order->remaining_amount);
                    }
                    $order->remaining_amount = 0;
                }
                $order->payment_status = $newStatus;
            }

            // 3. Pembaruan Logistik, Resi, Alamat, dan Catatan
            if ($request->filled('shipping_status')) $order->shipping_status = $request->shipping_status;
            if ($request->filled('tracking_number')) $order->tracking_number = $request->tracking_number;
            if ($request->filled('courier_name')) $order->courier_name = $request->courier_name;
            if ($request->filled('shipping_address')) $order->shipping_address = $request->shipping_address;
            if ($request->has('notes')) $order->notes = $request->notes;

            if ($request->filled('payment_proof_id')) {
                $order->payment_proof_id = $request->payment_proof_id;
                $this->setSingleAttachment($order, 'thumbnail', $request->payment_proof_id, 'payment_proof');
            }

            $order->save();

            return response()->json($order->load(['customer', 'items.book', 'thumbnail']));
        });
    }

    /**
     * PEMBAYARAN CICILAN / PELUNASAN PIUTANG
     */
    public function pay(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount'        => 'required|numeric|min:1', 
            'method'        => 'required|string', 
            'attachment_id' => 'nullable'
        ]);
        
        $order = Order::with('customer')->findOrFail($id);

        return DB::transaction(function () use ($order, $request) {
            $payAmount = min((float)$request->amount, (float)$order->remaining_amount);

            $order->decrement('remaining_amount', $payAmount);
            if ($order->customer && $order->payment_method === 'kredit') {
                $order->customer->decrement('current_debt', $payAmount);
            }

            if ($order->remaining_amount <= 0) {
                $order->update(['payment_status' => 'paid']);
            }

            DB::table('debt_payments')->insert([
                'id'             => Str::uuid(),
                'customer_id'    => $order->customer_id,
                'amount'         => $payAmount,
                'payment_method' => $request->method,
                'attachment_id'  => $request->attachment_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return response()->json([
                'message' => 'Pembayaran berhasil diverifikasi dan dicatat.',
                'order'   => $order->fresh(['customer', 'items.book'])
            ]);
        });
    }

    /**
     * BATALKAN PESANAN: Kembalikan Stok Buku & Potong Hutang (Jika Kredit)
     */
    public function cancel($id): JsonResponse 
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->payment_status === 'cancelled') {
            return response()->json(['message' => 'Pesanan ini sudah dibatalkan sebelumnya.'], 422);
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $book = Book::find($item->book_id);
                if ($book) {
                    $book->increment('stock', $item->qty);
                }
            }

            // Kurangi hutang hanya jika order ini berstatus kredit dan belum bayar
            if ($order->remaining_amount > 0 && $order->payment_method === 'kredit' && $order->payment_status !== 'waiting_shipping_fee') {
                $customer = Customer::withTrashed()->find($order->customer_id);
                if ($customer) {
                    $customer->decrement('current_debt', $order->remaining_amount);
                }
            }

            $order->update([
                'payment_status'   => 'cancelled',
                'shipping_status'  => 'cancelled',
                'remaining_amount' => 0
            ]);

            return response()->json(['message' => 'Pesanan berhasil dibatalkan dan stok buku telah dikembalikan.']);
        });
    }

    /**
     * CETAK PDF INVOICE RESMI ABC JAKARTA
     */
    public function downloadInvoice($id) 
    {
        $order = Order::with(['customer' => fn($q) => $q->withTrashed(), 'items.book'])->findOrFail($id);
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');

        $viewName = view()->exists('reports.invoice_pdf') ? 'reports.invoice_pdf' : 'reports.invoice';

        $pdf = Pdf::loadView($viewName, [
            'order'        => $order,
            'tanggalCetak' => $tanggalCetak
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }

    /**
     * CETAK PDF REKAP PENJUALAN BULANAN
     */
    public function exportMonthlyReport(Request $request) 
    {
        $query = Order::with(['customer' => fn($q) => $q->withTrashed(), 'items.book']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('type')) {
            $query->whereHas('customer', fn($q) => $q->where('type', $request->type));
        }

        $orders = $query->latest()->get();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $periode = Carbon::parse($request->start_date)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($request->end_date)->translatedFormat('d M Y');
        } elseif ($request->filled('start_date')) {
            $periode = 'Mulai ' . Carbon::parse($request->start_date)->translatedFormat('d M Y');
        } else {
            $periode = 'Bulan ' . Carbon::now()->translatedFormat('F Y');
        }

        $pdf = Pdf::loadView('reports.monthly_sales_pdf', [
            'orders'  => $orders,
            'periode' => $periode,
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Laporan-Penjualan-ABC-Jakarta-' . date('Y-m-d') . '.pdf');
    }
}