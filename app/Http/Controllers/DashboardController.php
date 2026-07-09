<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Book;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'stats' => [
                    'categories'    => Category::count(),
                    'customers'     => Customer::count(),
                    'books'         => Book::count(),
                    'new_orders'    => Order::where('payment_status', 'unpaid')->count(),
                    'low_stock'     => Book::whereColumn('stock', '<=', 'rop_point')->count(),
                    'total_piutang' => (int) Order::where('payment_status', 'unpaid')->sum('total_amount'),
                    'total_income'  => (int) Order::where('payment_status', 'paid')->sum('total_amount'),
                ],
                // Mengambil 5 transaksi terbaru untuk menghilangkan kesan "janggal/kosong"
                'recent_orders' => Order::with('customer')->latest()->take(5)->get(),
                
                // Data dummy grafik (nanti bisa dibuat dinamis)
                'monthly_sales' => [
                    ['label' => 'Mei', 'total' => 5000000],
                    ['label' => 'Jun', 'total' => 8500000],
                    ['label' => 'Jul', 'total' => (int) Order::where('payment_status', 'paid')->sum('total_amount')],
                ],
                'year' => date('Y'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}