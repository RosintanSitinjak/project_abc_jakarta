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

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::with('customer')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,transfer,kredit',
            'items' => 'required|array|min:1',
            'total_amount' => 'required'
        ]);

        // --- VALIDASI STOK (SKRIPSI LOGIC: CEGAH MINUS) ---
        foreach ($request->items as $item) {
            $book = Book::find($item['book_id']);
            if (!$book || $book->stock < $item['qty']) {
                return response()->json([
                    'message' => "Maaf, stok buku '{$book->title}' tidak mencukupi. Sisa: {$book->stock}"
                ], 422);
            }
        }

        return DB::transaction(function () use ($request) {
            $customer = Customer::find($request->customer_id);

            // 1. Buat Header Order
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'kredit' ? 'unpaid' : 'paid',
                'shipping_status' => 'pending',
                'source' => $request->input('source', 'admin_manual'),
                'created_at' => $request->date,
            ]);

            // 2. Tambah Hutang Jika Kredit
            if ($order->payment_status === 'unpaid') {
                $customer->increment('current_debt', $order->total_amount);
            }

            // 3. Simpan Detail & Potong Stok
            foreach ($request->items as $item) {
                $book = Book::find($item['book_id']);
                
                // Harga PL vs Umum
                $finalPrice = ($customer->type === 'penginjil') ? ($book->member_price ?? $book->price) : $book->price;

                $order->items()->create([
                    'book_id' => $book->id,
                    'qty' => $item['qty'],
                    'price_at_purchase' => $finalPrice,
                ]);

                $book->decrement('stock', $item['qty']);
                
                // Trigger Notifikasi ROP
                if ($book->stock <= $book->rop_point) {
                    Log::info("🚨 ROP ALERT: Buku '{$book->title}' sisa {$book->stock}.");
                }
            }

            return response()->json($order->load('customer'), 201);
        });
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;
        $order->update($request->only(['payment_status', 'shipping_status']));

        // Kurangi hutang jika dari Belum Bayar menjadi Lunas
        if ($oldStatus === 'unpaid' && $order->payment_status === 'paid') {
            $order->customer->decrement('current_debt', $order->total_amount);
        }

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order->payment_status === 'unpaid') {
            $order->customer->decrement('current_debt', $order->total_amount);
        }
        $order->delete();
        return response()->json(['status' => 'deleted']);
    }
}