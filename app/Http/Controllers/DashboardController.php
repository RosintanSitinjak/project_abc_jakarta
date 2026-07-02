<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Book;
use App\Models\Order;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
       return response()->json([
        'stats' => [
            'services'  => \App\Models\Category::count(),
            'clients'   => \App\Models\Customer::count(),
            'products'  => \App\Models\Book::count(),
            'portfolios'=> \App\Models\Order::where('payment_status', 'pending')->count(), // Pesanan Baru
            'visitors'  => \App\Models\Book::whereRaw('stock <= rop_point')->count(), // Stok Kritis
        ],
        // Untuk grafik, sementara biarkan data dummy atau sesuaikan jika sudah ada tabel transaksi
        'monthly_visitors' => [
            ['label' => 'Jan', 'total' => 0],
            ['label' => 'Feb', 'total' => 0],
            // ... dst
        ],
        'year' => date('Year'),
    ]);
    }
}