<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
// Pastikan model-model ini di-import dengan benar
use App\Models\Category;
use App\Models\Customer;
use App\Models\Book;
use App\Models\Order;

class DashboardController extends Controller
{
public function index(): \Illuminate\Http\JsonResponse
{
    // Menggunakan try-catch agar jika error, kita bisa lihat pesannya di browser
    try {
return response()->json([
    'stats' => [
        'services'   => \App\Models\Category::count(), // Muncul di card KATEGORI
        'clients'    => \App\Models\Customer::count(), // Muncul di card GEREJA & JEMAAT
        'products'   => \App\Models\Book::count(),     // Muncul di card STOK BUKU
        'portfolios' => \App\Models\Order::count(),    // Muncul di card PESANAN BARU
        'visitors'   => \App\Models\Book::whereColumn('stock', '<=', 'rop_point')->count(), // STOK KRITIS
    ],
    'monthly_visitors' => [],
    'year' => date('Y'),
]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}