<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $storeId = auth()->user()->store_id;

        $stats = [
            'total_products' => \App\Models\Product::where('store_id', $storeId)->count(),
            'active_products' => \App\Models\Product::where('store_id', $storeId)->where('active', true)->count(),
            'low_stock' => \App\Models\Product::where('store_id', $storeId)->where('stock', '<', 5)->count(),
            'total_sales' => \App\Models\Transaction::where('store_id', $storeId)->whereIn('status', ['completed'])->sum('total'),
        ];

        $salesToday = (float) Transaction::where('store_id', $storeId)->whereDate('created_at', $today)->sum('total');
        $transactionsToday = Transaction::where('store_id', $storeId)->whereDate('created_at', $today)->count();
        $productsCount = Product::where('store_id', $storeId)->count();
        $lowStockProducts = Product::where('store_id', $storeId)->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get();

        $salesSummary = Transaction::where('store_id', $storeId)->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date = Carbon::parse($item->date)->format('d M');
                return $item;
            });

        // ==========================================================
        // 1. ANALISIS PRODUK TERLARIS (Top 5)
        // ==========================================================
        $topProducts = TransactionDetail::whereHas('transaction', function($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(subtotal) as total_revenue')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->total_revenue = (float) $item->total_revenue;
                return $item;
            });

        // ==========================================================
        // 2. ANALISIS TREN PENJUALAN + INSIGHT
        // ==========================================================
        $salesTrend = Transaction::where('store_id', $storeId)->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendTotals = $salesTrend->pluck('total')->map(fn ($v) => (float) $v);

        $last7 = $trendTotals->sum();
        $prev7 = (float) Transaction::where('store_id', $storeId)->whereBetween('created_at', [Carbon::now()->subDays(13)->startOfDay(), Carbon::now()->subDays(7)->startOfDay()])
            ->sum('total');

        $growth = $prev7 > 0 ? round((($last7 - $prev7) / $prev7) * 100, 1) : ($last7 > 0 ? 100 : 0);

        $bestDay = $salesTrend->sortByDesc('total')->first();
        $trendDirection = $growth >= 0 ? 'naik' : 'turun';

        $salesTrendInsight = $this->buildTrendInsight($bestDay, $growth, $last7);

        // ==========================================================
        // 3. PREDIKSI KEBUTUHAN STOK
        // ==========================================================
        $stockPredictions = $this->buildStockPredictions();

        // ==========================================================
        // 4. ANALISIS KEUNTUNGAN USAHA
        // ==========================================================
        $profit = $this->buildProfitSummary();

        // ==========================================================
        // 5. PELANGGAN YANG PERLU DITINDAKLANJUTI
        // Pelanggan dianggap rutin bila berbelanja pada minimal tiga
        // hari berbeda dalam 30 hari terakhir, tetapi belum membeli hari ini.
        // ==========================================================
        $followUpCustomers = $this->buildFollowUpCustomers($today);

        return view('dashboard', compact(
            'salesToday',
            'transactionsToday',
            'productsCount',
            'lowStockProducts',
            'salesSummary',
            'topProducts',
            'salesTrendInsight',
            'trendDirection',
            'growth',
            'stockPredictions',
            'profit',
            'followUpCustomers'
        ));
    }

    /**
     * Bangun insight tren penjualan 7 hari.
     */
    protected function buildTrendInsight($bestDay, float $growth, float $last7): array
    {
        $bestLabel = $bestDay && $bestDay->total
            ? Carbon::parse($bestDay->date)->locale('id')->translatedFormat('l')
            : '-';
        $bestDate = $bestDay && $bestDay->total
            ? Carbon::parse($bestDay->date)->locale('id')->translatedFormat('d M')
            : '-';
        $bestTotal = $bestDay && $bestDay->total ? (float) $bestDay->total : 0;

        return [
            'best_day' => $bestLabel,
            'best_date' => $bestDate,
            'best_total' => $bestTotal,
            'growth' => $growth,
            'last7' => $last7,
        ];
    }

    /**
     * Prediksi kebutuhan stok: estimasi hari stok habis berdasarkan rata-rata penjualan 30 hari.
     */
    protected function buildStockPredictions(): array
    {
        $period = 30;
        $storeId = auth()->user()->store_id;
        $since = Carbon::now()->subDays($period)->startOfDay();

        return Product::where('store_id', $storeId)->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(5)
            ->get()
            ->map(function ($product) use ($since, $period) {
                $sold = (int) TransactionDetail::where('product_id', $product->id)
                    ->whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $since))
                    ->sum('quantity');

                $avgDaily = $period > 0 ? ($sold / $period) : 0;
                $daysLeft = $avgDaily > 0 ? (int) floor($product->stock / $avgDaily) : null;

                return [
                    'product' => $product,
                    'sold_30d' => $sold,
                    'avg_daily' => round($avgDaily, 2),
                    'days_left' => $daysLeft,
                ];
            })
            ->all();
    }

    /**
     * Ringkasan keuntungan usaha (revenue, modal, laba, margin).
     */
    protected function buildProfitSummary(): array
    {
        $income = (float) Transaction::sum('total');

        $costRow = TransactionDetail::join('products', 'transaction_details.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(transaction_details.quantity * products.purchase_price), 0) as cost')
            ->first();

        $cost = (float) ($costRow->cost ?? 0);
        $netProfit = $income - $cost;
        $margin = $income > 0 ? round(($netProfit / $income) * 100, 1) : 0;

        return [
            'revenue' => $income,
            'cost' => $cost,
            'net_profit' => $netProfit,
            'margin' => $margin,
        ];
    }

    /**
     * Pelanggan rutin yang belum melakukan transaksi pada hari ini.
     */
    protected function buildFollowUpCustomers(Carbon $today)
    {
        $since = $today->copy()->subDays(29)->startOfDay();

        return Customer::query()
            ->select('customers.id', 'customers.name', 'customers.phone')
            ->selectRaw('COUNT(DISTINCT DATE(transactions.created_at)) as purchase_days')
            ->selectRaw('COUNT(transactions.id) as transaction_count')
            ->selectRaw('MAX(transactions.created_at) as last_purchase_at')
            ->join('transactions', 'transactions.customer_id', '=', 'customers.id')
            ->where('transactions.created_at', '>=', $since)
            ->whereNotNull('customers.phone')
            ->where('customers.phone', '!=', '')
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('transactions as today_transactions')
                    ->whereColumn('today_transactions.customer_id', 'customers.id')
                    ->whereDate('today_transactions.created_at', $today);
            })
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->havingRaw('COUNT(DISTINCT DATE(transactions.created_at)) >= 3')
            ->orderByDesc('purchase_days')
            ->orderByDesc('last_purchase_at')
            ->limit(10)
            ->get();
    }
}
