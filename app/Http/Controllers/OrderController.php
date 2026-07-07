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
        // Load relasi customer agar nama pelanggan muncul di tabel
        return response()->json(Order::with('customer')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'payment_method' => 'required',
            'items' => 'required|array',
            'total_amount' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'kredit' ? 'unpaid' : 'paid',
                'shipping_status' => 'pending',
                'source' => 'admin_manual',
            ]);

            foreach ($request->items as $item) {
                $order->items()->create([
                    'book_id' => $item['book_id'],
                    'qty' => $item['qty'],
                    'price_at_purchase' => $item['price'],
                ]);

                // Potong Stok
                $book = Book::find($item['book_id']);
                if ($book) {
                    $book->decrement('stock', $item['qty']);
                }
            }

            return response()->json($order->load('customer'), 201);
        });
    }

    // --- FUNGSI UPDATE STATUS (UNTUK VERIFIKASI) ---
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Hanya izinkan update status pembayaran dan pengiriman
        $order->update($request->only(['payment_status', 'shipping_status']));

        return response()->json($order);
    }

    // --- FUNGSI HAPUS TRANSAKSI ---
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete(); // Karena pakai SoftDeletes, data tidak hilang dari DB tapi hilang dari tabel web

        return response()->json(['status' => 'deleted']);
    }
}