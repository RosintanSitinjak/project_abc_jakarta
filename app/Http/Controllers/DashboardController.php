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

            // 1. HITUNG TOTAL PIUTANG RESMI (Hanya Pesanan Kredit Mitra yang Belum Lunas)
            // Ini membuat angka Total Piutang di Dashboard SINKRON 100% dengan Menu Buku Piutang!
            $totalPiutang = (int) Order::whereIn('payment_method', ['kredit', 'credit'])
                ->where('payment_status', '!=', 'cancelled')
                ->sum('remaining_amount');

            // Realisasi Kas Masuk Riil dari Seluruh Pembayaran yang Sudah Terjadi
            $totalIncome = (int) Order::select(DB::raw('SUM(total_amount - remaining_amount) as income'))->value('income');

            // 2. LOGIKA AGING PIUTANG KHUSUS KREDIT MITRA (Analisis Kolektibilitas ABC Jakarta)
            // Piutang Macet: Sisa tagihan kredit yang berumur lebih dari 30 hari
            $piutangMacet = (int) Order::whereIn('payment_method', ['kredit', 'credit'])
                ->where('remaining_amount', '>', 0)
                ->where('payment_status', '!=', 'cancelled')
                ->where('created_at', '<=', Carbon::now()->subDays(30))
                ->sum('remaining_amount');

            // Mendekati Jatuh Tempo: Sisa tagihan kredit berumur antara 14 s/d 30 hari
            $piutangTempo = (int) Order::whereIn('payment_method', ['kredit', 'credit'])
                ->where('remaining_amount', '>', 0)
                ->where('payment_status', '!=', 'cancelled')
                ->where('created_at', '>', Carbon::now()->subDays(30))
                ->where('created_at', '<=', Carbon::now()->subDays(14))
                ->sum('remaining_amount');

            // 3. STATISTIK PENJUALAN BULANAN (Dihitung Dinamis per Bulan di Tahun Berjalan)
            $currentYear = $now->year;
            $currentMonth = $now->month;
            $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

            $salesPerMonth = Order::select(
                DB::raw("CAST(EXTRACT(MONTH FROM created_at) AS INTEGER) as month"),
                DB::raw("SUM(total_amount) as total")
            )
            ->whereYear('created_at', $currentYear)
            ->where('payment_status', '!=', 'cancelled')
            ->groupBy(DB::raw("EXTRACT(MONTH FROM created_at)"))
            ->pluck('total', 'month')
            ->toArray();

            $monthlySales = [];
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
                    'new_orders'    => Order::where('payment_status', 'unpaid')->count(),
                    'low_stock'     => Book::whereColumn('stock', '<=', 'rop_point')->count(),
                    'total_piutang' => $totalPiutang,
                    'total_income'  => $totalIncome,
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