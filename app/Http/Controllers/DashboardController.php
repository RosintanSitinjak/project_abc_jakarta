<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $now = Carbon::now();

            // 1. Hitung Total Piutang & Pemasukan (Sesuai update kemarin)
            $totalPiutang = (int) Order::sum('remaining_amount');
            $totalIncome = (int) Order::select(DB::raw('SUM(total_amount - remaining_amount) as income'))->value('income');

            // 2. LOGIKA AGING PIUTANG (Nilai Tambah Skripsi)
            // Macet: Sisa tagihan yang umurnya > 30 hari
            $piutangMacet = (int) Order::where('remaining_amount', '>', 0)
                ->where('created_at', '<=', $now->subDays(30))
                ->sum('remaining_amount');

            // Mendekati Tempo: Sisa tagihan yang umurnya 14 - 30 hari
            $piutangTempo = (int) Order::where('remaining_amount', '>', 0)
                ->where('created_at', '>', Carbon::now()->subDays(30))
                ->where('created_at', '<=', Carbon::now()->subDays(14))
                ->sum('remaining_amount');

            // 3. Statistik Penjualan Juli
            $juliSales = (int) Order::whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->sum('total_amount');

            return response()->json([
                'stats' => [
                    'categories'    => Category::count(),
                    'customers'     => Customer::count(),
                    'books'         => Book::count(),
                    'new_orders'    => Order::where('remaining_amount', '>', 0)->count(),
                    'low_stock'     => Book::whereColumn('stock', '<=', 'rop_point')->count(),
                    'total_piutang' => $totalPiutang,
                    'total_income'  => $totalIncome,
                    // Data Aging
                    'aging_macet'   => $piutangMacet,
                    'aging_tempo'   => $piutangTempo,
                ],
                'recent_orders' => Order::with('customer')->latest()->take(5)->get(),
                'monthly_sales' => [
                    ['label' => 'Mei', 'total' => 5000000],
                    ['label' => 'Jun', 'total' => 8500000],
                    ['label' => 'Jul', 'total' => $juliSales],
                ],
                'year' => date('Y'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}