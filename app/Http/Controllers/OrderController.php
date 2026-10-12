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
            'payment_method' => 'required',
            'items' => 'required|array',
            'total_amount' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $customer = Customer::find($request->customer_id);

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

            // LOGIKA SKRIPSI: Jika beli Kredit, tambahkan ke saldo hutang pelanggan
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

                if ($book) {
                    $book->decrement('stock', $item['qty']);
                    if ($book->stock <= $book->rop_point) {
                        Log::info("🚨 ROP ALERT: Buku '{$book->title}' sisa {$book->stock}.");
                    }
                }
            }

            return response()->json($order->load('customer'), 201);
        });
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Simpan status lama sebelum diupdate
        $oldPaymentStatus = $order->payment_status;

        $order->update($request->only(['payment_status', 'shipping_status']));

        // LOGIKA SKRIPSI: Jika status berubah dari BELUM BAYAR ke LUNAS, kurangi hutang pelanggan
        if ($oldPaymentStatus === 'unpaid' && $order->payment_status === 'paid') {
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $customer->decrement('current_debt', $order->total_amount);
            }
        }

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        return DB::transaction(function () use ($order) {
            // Jika pesanan yang dihapus berstatus Belum Bayar, kurangi saldo hutang dulu
            if ($order->payment_status === 'unpaid') {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->decrement('current_debt', $order->total_amount);
                }
            }
            
            $order->delete();
            return response()->json(['status' => 'deleted']);
        });
    }
}