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

    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['customer' => fn($q) => $q->withTrashed(), 'items.book', 'thumbnail']);
        
        // Filter Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Filter Spesifik (PENTING untuk Buku Piutang)
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        
        if ($request->filled('type')) {
            $query->whereHas('customer', fn($q) => $q->where('type', $request->type));
        }

        // Pencarian Nama atau No Invoice
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required',
            'items' => 'required|array',
            'total_amount' => 'required',
        ]);

        return DB::transaction(function () use ($request) {
            $customer = Customer::find($request->customer_id);
            $paidAtStart = $request->input('paid_amount', 0);
            $remaining = $request->total_amount - $paidAtStart;

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $request->total_amount,
                'remaining_amount' => $remaining,
                'payment_method' => $request->payment_method,
                'payment_status' => ($remaining <= 0) ? 'paid' : 'unpaid',
                'payment_proof_id' => $request->payment_proof_id,
                'source' => 'admin_manual',
                'created_at' => $request->date ?? now(),
                'due_date' => Carbon::parse($request->date ?? now())->addDays(30),
            ]);

            if ($request->filled('payment_proof_id')) {
                $this->setSingleAttachment($order, 'thumbnail', $request->payment_proof_id, 'payment_proof');
            }

            if ($remaining > 0) $customer->increment('current_debt', $remaining);

            if ($paidAtStart > 0) {
                DB::table('debt_payments')->insert([
                    'id' => Str::uuid(), 'customer_id' => $customer->id, 'amount' => $paidAtStart,
                    'payment_method' => $request->payment_method, 'attachment_id' => $request->payment_proof_id ?? null,
                    'created_at' => $order->created_at, 'updated_at' => now(),
                ]);
            }

            foreach ($request->items as $item) {
                $book = Book::find($item['book_id']);
                $finalPrice = ($customer->type === 'penginjil') ? ($book->member_price ?? $book->price) : $book->price;
                $order->items()->create(['book_id' => $book->id, 'qty' => $item['qty'], 'price_at_purchase' => $finalPrice]);
                $book->decrement('stock', $item['qty']);
            }
            return response()->json($order->load(['customer', 'items.book']), 201);
        });
    }

    public function pay(Request $request, $id): JsonResponse
    {
        $request->validate(['amount' => 'required|integer|min:1', 'method' => 'required|string', 'attachment_id' => 'nullable']);
        
        $order = Order::with('customer')->findOrFail($id);

        return DB::transaction(function () use ($order, $request) {
            $order->decrement('remaining_amount', $request->amount);
            $order->customer->decrement('current_debt', $request->amount);

            if ($order->remaining_amount <= 0) {
                $order->update(['payment_status' => 'paid']);
            }

            DB::table('debt_payments')->insert([
                'id' => Str::uuid(),
                'customer_id' => $order->customer_id,
                'amount' => $request->amount,
                'payment_method' => $request->method,
                'attachment_id' => $request->attachment_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Ok', 'order' => $order->fresh()]);
        });
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;
        $order->update($request->only(['payment_status', 'shipping_status', 'tracking_number', 'courier_name']));
        if ($request->filled('payment_proof_id')) {
            $order->payment_proof_id = $request->payment_proof_id; $order->save();
            $this->setSingleAttachment($order, 'thumbnail', $request->payment_proof_id, 'payment_proof');
        }
        if ($oldStatus === 'unpaid' && $order->payment_status === 'paid') {
            $customer = Customer::withTrashed()->find($order->customer_id);
            $customer->decrement('current_debt', $order->remaining_amount);
            $order->update(['remaining_amount' => 0]);
        }
        return response()->json($order->load(['customer', 'items.book', 'thumbnail']));
    }

    public function cancel($id): JsonResponse { /* ... sama ... */ }
    public function downloadInvoice($id) { /* ... sama ... */ }
    public function exportMonthlyReport(Request $request) { /* ... sama ... */ }
}