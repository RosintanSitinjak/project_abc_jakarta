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

            // 1. Hitung Total Piutang & Realisasi Pemasukan Kas
            $totalPiutang = (int) Order::sum('remaining_amount');
            $totalIncome = (int) Order::select(DB::raw('SUM(total_amount - remaining_amount) as income'))->value('income');

            // 2. LOGIKA AGING PIUTANG (Analisis Kolektibilitas ABC Jakarta)
            // Piutang Macet: Sisa tagihan berumur lebih dari 30 hari
            $piutangMacet = (int) Order::where('remaining_amount', '>', 0)
                ->where('created_at', '<=', Carbon::now()->subDays(30))
                ->sum('remaining_amount');

            // Mendekati Jatuh Tempo: Sisa tagihan berumur antara 14 s/d 30 hari
            $piutangTempo = (int) Order::where('remaining_amount', '>', 0)
                ->where('created_at', '>', Carbon::now()->subDays(30))
                ->where('created_at', '<=', Carbon::now()->subDays(14))
                ->sum('remaining_amount');

            // 3. STATISTIK PENJUALAN BULANAN (Dihitung Dinamis dari Database PostgreSQL)
            $currentYear = $now->year;
            $currentMonth = $now->month;
            $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

            // Query omzet penjualan per bulan di tahun berjalan
            $salesPerMonth = Order::select(
                DB::raw("CAST(EXTRACT(MONTH FROM created_at) AS INTEGER) as month"),
                DB::raw("SUM(total_amount) as total")
            )
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw("EXTRACT(MONTH FROM created_at)"))
            ->pluck('total', 'month')
            ->toArray();

            $monthlySales = [];
            // Menampilkan riwayat bulan berjalan (misal: Jan s/d September)
            for ($m = 1; $m <= $currentMonth; $m++) {
                $monthlySales[] = [
                    'label' => $monthNames[$m],
                    'total' => (int) ($salesPerMonth[$m] ?? 0),
                ];
            }

            // 4. DAFTAR BUKU STOK KRITIS (Algoritma ROP)
            $criticalBooks = Book::whereColumn('stock', '<=', 'rop_point')
                ->select('id', 'title', 'stock', 'rop_point')
                ->orderBy('stock', 'asc')
                ->take(3)
                ->get();

            return response()->json([
                'stats' => [
                    'categories'    => Category::count(),
                    'customers'     => Customer::count(),
                    'books'         => Book::count(),
                    'new_orders'    => Order::where('remaining_amount', '>', 0)->count(),
                    'low_stock'     => Book::whereColumn('stock', '<=', 'rop_point')->count(),
                    'total_piutang' => $totalPiutang,
                    'total_income'  => $totalIncome,
                    // Data Analisis Kolektibilitas
                    'aging_macet'   => $piutangMacet,
                    'aging_tempo'   => $piutangTempo,
                ],
                'critical_books' => $criticalBooks,
                'recent_orders'  => Order::with('customer')->latest()->take(5)->get(),
                'monthly_sales'  => $monthlySales,
                'year'           => $currentYear,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}