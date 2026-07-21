<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Concerns\ManagesAttachments;

class OrderController extends Controller
{
    use ManagesAttachments; // WAJIB ada untuk fitur unggah bukti

        public function index(Request $request): JsonResponse
{
    // 1. Mulai Query dengan relasi yang dibutuhkan
 $query = Order::with([
        'customer' => function($q) {
            $q->withTrashed(); // <--- INI KUNCINYA: Ambil profil meski sudah dihapus
        }, 
        'items.book', 
        'thumbnail'
    ]);
    
    // 2. Filter berdasarkan Metode Pembayaran (misal: 'kredit')
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }

    // 3. Filter berdasarkan Status Pembayaran (misal: 'unpaid')
    if ($request->filled('payment_status')) {
        $query->where('payment_status', $request->payment_status);
    }

    // 4. Filter berdasarkan Tipe Pelanggan (Gereja/PL/Jemaat) melalui tabel Customer
    if ($request->filled('type')) {
        $query->whereHas('customer', function($q) use ($request) {
            $q->where('type', $request->type);
        });
    }

    // 5. Pencarian berdasarkan Nomor Invoice atau Nama Pelanggan
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('order_number', 'ILIKE', "%{$search}%")
              ->orWhereHas('customer', function($q2) use ($search) {
                  $q2->where('name', 'ILIKE', "%{$search}%");
              });
        });
    }

    $orders = $query->latest()->get();

    // Transformasi URL Gambar (thumbnail)
    $orders->transform(function ($order) {
        $order->payment_proof = $order->thumbnail ? asset('storage/' . $order->thumbnail->path) : null;
        return $order;
    });

    return response()->json($orders);
}

        // ... (bagian atas tetap sama)

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,transfer,kredit',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.qty' => 'required|integer|min:1',
            'total_amount' => 'required'
        ]);

        foreach ($request->items as $item) {
            $book = Book::find($item['book_id']);
            if (!$book || $book->stock < $item['qty']) {
                return response()->json(['message' => "Stok buku '{$book->title}' tidak mencukupi."], 422);
            }
        }

        return DB::transaction(function () use ($request) {
            $customer = Customer::find($request->customer_id);

            // HEADER ORDER
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $request->total_amount,
                
                // --- FIX DI SINI: Isi sisa tagihan sama dengan total belanja di awal ---
                'remaining_amount' => ($request->payment_method === 'kredit') ? $request->total_amount : 0,
                
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'kredit' ? 'unpaid' : 'paid',
                'shipping_status' => 'pending',
                'source' => 'admin_manual',
                'created_at' => $request->date ?? now(),
            ]);

            if ($order->payment_status === 'unpaid') {
                $customer->increment('current_debt', $order->total_amount);
            }

            foreach ($request->items as $item) {
                $book = Book::find($item['book_id']);
                $finalPrice = ($customer->type === 'penginjil') ? ($book->member_price ?? $book->price) : $book->price;

                $order->items()->create([
                    'book_id' => $book->id,
                    'qty' => $item['qty'],
                    'price_at_purchase' => $finalPrice,
                ]);

                $book->decrement('stock', $item['qty']);
            }

            return response()->json($order->load(['customer', 'items.book']), 201);
        });
    }

// ... (fungsi update, destroy, pay tetap sama dengan versi kamu)


public function update(Request $request, $id): JsonResponse
{
    $order = Order::findOrFail($id);
    $oldStatus = $order->payment_status;

    // 1. Update data dasar (Status, Resi, Kurir)
    $order->update($request->only([
        'payment_status', 
        'shipping_status', 
        'tracking_number', 
        'courier_name'
    ]));

    // 2. LOGIKA FIXING (INTI MASALAH):
    if ($request->filled('payment_proof_id')) {
        // Kita simpan ID-nya langsung ke kolom payment_proof_id di tabel orders
        $order->payment_proof_id = $request->payment_proof_id;
        $order->save(); // <--- WAJIB SAVE agar ID-nya tidak 'terbang'

        // Jalankan perintah attachment untuk sinkronisasi relasi polimorfis
        $this->setSingleAttachment($order, 'thumbnail', $request->payment_proof_id, 'payment_proof');
    }

    // 3. Sinkronisasi Piutang
    if ($oldStatus === 'unpaid' && $order->payment_status === 'paid') {
        $order->customer->decrement('current_debt', $order->total_amount);
    }

    // Load ulang semua relasi termasuk thumbnail agar frontend dapet data terbaru
    return response()->json($order->load(['customer', 'items.book', 'thumbnail']));
}


    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        return DB::transaction(function () use ($order) {
            if ($order->payment_status === 'unpaid') {
                $order->customer->decrement('current_debt', $order->total_amount);
            }
            $order->delete();
            return response()->json(['status' => 'deleted']);
        });
    }

    /**
 * FUNGSI: Bayar Cicilan Per Invoice
 */
public function pay(Request $request, $id): JsonResponse
{
    $request->validate([
        'amount' => 'required|integer|min:1',
        'method' => 'required|string'
    ]);

    $order = Order::with('customer')->findOrFail($id);

    // Proteksi: Jangan sampai bayar melebihi sisa tagihan
    if ($request->amount > $order->remaining_amount) {
        return response()->json(['message' => 'Jumlah bayar melebihi sisa tagihan!'], 422);
    }

    return DB::transaction(function () use ($order, $request) {
        // 1. Kurangi Sisa Tagihan di Invoice ini
        $order->decrement('remaining_amount', $request->amount);

        // 2. Kurangi Saldo Hutang Global di tabel Pelanggan
        $order->customer->decrement('current_debt', $request->amount);

        // 3. Jika Sisa Tagihan jadi 0, otomatis set status jadi LUNAS
        if ($order->remaining_amount <= 0) {
            $order->update(['payment_status' => 'paid']);
        }

        // 4. Catat sejarah di tabel debt_payments (agar terekam di Kartu Piutang)
        DB::table('debt_payments')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'customer_id' => $order->customer_id,
            'amount' => $request->amount,
            'payment_method' => $request->method,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Pembayaran cicilan berhasil dicatat',
            'order' => $order->fresh()
        ]);
    });
}
}