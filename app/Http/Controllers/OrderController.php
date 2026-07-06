<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Book;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        // Menampilkan daftar pesanan beserta nama pelanggannya
        return response()->json(Order::with('customer')->latest()->get());
    }

    public function store(Request $request)
    {
        // Validasi data yang dikirim dari Nuxt
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,transfer,kredit',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Buat Header Pesanan
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'kredit' ? 'unpaid' : 'paid',
                'source' => 'admin_manual', // Sesuai saran owner: tandai pesanan manual
            ]);

            // 2. Simpan Detail Buku & Potong Stok
            foreach ($request->items as $item) {
                // Catat ke tabel detail (order_items)
                $order->items()->create([
                    'book_id' => $item['book_id'],
                    'qty' => $item['qty'],
                    'price_at_purchase' => $item['price'],
                ]);

                // POTONG STOK BUKU
                $book = Book::find($item['book_id']);
                $book->decrement('stock', $item['qty']);

                // --- CEK ROP SETIAP HABIS JUALAN ---
                if ($book->stock <= $book->rop_point) {
                    Log::info("🚨 ROP TRIGGERED (JUALAN MANUAL): Buku {$book->title} sisa {$book->stock}. Segera restock!");
                }
            }

            return response()->json($order->load('customer'), 201);
        });
    }
}