<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashFlowRequest;
use App\Models\CashFlow;
use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $storeId = auth()->user()->store_id;
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Transaction::with(['user', 'customer'])
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();
        $cashFlows = CashFlow::with(['user', 'product'])
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        $sales = (float) $transactions->sum('total');
        $capital = (float) $cashFlows->where('type', CashFlow::CAPITAL)->sum('amount');
        $expenses = (float) $cashFlows->whereIn('type', [CashFlow::EXPENSE, CashFlow::STOCK_PURCHASE])->sum('amount');
        $openingBalance = $this->cashBalanceBefore($start);
        $businessHealth = $this->buildBusinessHealth($start, $end, $sales, $capital, $expenses, $openingBalance);

        $history = $this->history($transactions, $cashFlows);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $entries = new LengthAwarePaginator(
            $history->forPage($page, $perPage)->values(),
            $history->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('reports.index', compact('entries', 'start', 'end', 'sales', 'capital', 'expenses', 'openingBalance', 'businessHealth'));
    }

    public function storeCashFlow(StoreCashFlowRequest $request)
    {
        CashFlow::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id,
        ]);

        return redirect()->route('reports.index')->with('success', 'Mutasi kas berhasil dicatat.');
    }

    public function exportPdf(Request $request)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();
        $transactions = Transaction::with(['user', 'customer'])
            ->where('store_id', auth()->user()->store_id)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $pdf = Pdf::loadView('reports.pdf', compact('transactions', 'start', 'end'));

        return $pdf->download('laporan-penjualan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();

        return Excel::download(new TransactionsExport($start, $end), 'laporan-penjualan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.xlsx');
    }

    private function cashBalanceBefore(Carbon $start): float
    {
        $storeId = auth()->user()->store_id;
        $sales = (float) Transaction::where('store_id', $storeId)->where('created_at', '<', $start)->sum('total');
        $capital = (float) CashFlow::where('store_id', $storeId)->where('created_at', '<', $start)->where('type', CashFlow::CAPITAL)->sum('amount');
        $expenses = (float) CashFlow::where('store_id', $storeId)->where('created_at', '<', $start)
            ->whereIn('type', [CashFlow::EXPENSE, CashFlow::STOCK_PURCHASE])
            ->sum('amount');

        return $sales + $capital - $expenses;
    }

    /**
     * Ringkasan kesehatan usaha dari data kas dan omzet pada periode aktif.
     */
    private function buildBusinessHealth(Carbon $start, Carbon $end, float $sales, float $capital, float $expenses, float $openingBalance): array
    {
        $storeId = auth()->user()->store_id;
        $days = max(1, $start->diffInDays($end) + 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();
        $previousSales = (float) Transaction::where('store_id', $storeId)->whereBetween('created_at', [$previousStart, $previousEnd])->sum('total');
        $salesChange = $previousSales > 0
            ? round((($sales - $previousSales) / $previousSales) * 100, 1)
            : ($sales > 0 ? 100.0 : 0.0);
        $cashChange = $sales + $capital - $expenses;
        $closingBalance = $openingBalance + $cashChange;
        $expenseRatio = $sales > 0 ? round(($expenses / $sales) * 100, 1) : null;
        $lowStockCount = Product::where('store_id', $storeId)->where('stock', '<=', 5)->count();

        $trend = $salesChange > 0 ? 'naik' : ($salesChange < 0 ? 'turun' : 'stabil');
        $score = 50;
        $score += $salesChange >= 10 ? 20 : ($salesChange > 0 ? 10 : ($salesChange <= -10 ? -20 : 0));
        $score += $cashChange > 0 ? 15 : ($cashChange < 0 ? -15 : 0);
        $score += $expenseRatio !== null && $expenseRatio <= 60 ? 10 : ($expenseRatio !== null && $expenseRatio > 90 ? -10 : 0);
        $score -= min($lowStockCount * 3, 15);
        $score = max(0, min(100, $score));

        $status = $score >= 75 ? ['Sangat sehat', 'success', '🌟'] : ($score >= 55 ? ['Cukup sehat', 'primary', '🙂'] : ['Perlu perhatian', 'warning', '💪']);
        $suggestions = [];
        $suggestions[] = $trend === 'naik'
            ? 'Omzet naik. Pertahankan stok produk yang paling cepat laku dan promosikan paket belanja. 🚀'
            : ($trend === 'turun'
                ? 'Omzet turun. Coba promo sederhana untuk kebutuhan harian dan ingatkan pelanggan tetap. 📣'
                : 'Omzet masih stabil. Buat promo kecil agar pelanggan punya alasan untuk berbelanja lebih banyak. ✨');
        if ($expenseRatio !== null && $expenseRatio > 70) {
            $suggestions[] = 'Kas keluar mencapai '.$expenseRatio.'% dari omzet. Tinjau pengeluaran dan pembelian stok agar modal tetap aman. 💰';
        } elseif ($cashChange > 0) {
            $suggestions[] = 'Arus kas periode ini positif. Sisihkan sebagian surplus sebagai dana cadangan warung. 🐷';
        }
        if ($lowStockCount > 0) {
            $suggestions[] = $lowStockCount.' produk stoknya menipis. Prioritaskan pengadaan barang yang paling sering dicari. 📦';
        }

        return [
            'score' => $score,
            'status' => $status[0],
            'color' => $status[1],
            'emoji' => $status[2],
            'trend' => $trend,
            'sales_change' => $salesChange,
            'previous_sales' => $previousSales,
            'cash_change' => $cashChange,
            'closing_balance' => $closingBalance,
            'expense_ratio' => $expenseRatio,
            'low_stock_count' => $lowStockCount,
            'suggestions' => $suggestions,
        ];
    }

    private function history(Collection $transactions, Collection $cashFlows): Collection
    {
        $sales = $transactions->map(fn (Transaction $transaction) => (object) [
            'date' => $transaction->created_at,
            'description' => "Penjualan {$transaction->invoice}",
            'user' => $transaction->user->name,
            'type' => 'Penjualan Kasir',
            'category' => 'sale',
            'in' => (float) $transaction->total,
            'out' => 0,
        ]);

        $cash = $cashFlows->map(fn (CashFlow $flow) => (object) [
            'date' => $flow->created_at,
            'description' => $flow->description,
            'user' => $flow->user->name,
            'type' => match ($flow->type) {
                CashFlow::CAPITAL => 'Tambah Dana',
                CashFlow::STOCK_PURCHASE => 'Pembelian Stok Produk',
                default => 'Pengeluaran',
            },
            'category' => match ($flow->type) {
                CashFlow::CAPITAL => 'capital',
                CashFlow::STOCK_PURCHASE => 'stock',
                default => 'expense',
            },
            'in' => $flow->type === CashFlow::CAPITAL ? (float) $flow->amount : 0,
            'out' => $flow->type === CashFlow::CAPITAL ? 0 : (float) $flow->amount,
        ]);

        return $sales->concat($cash)->sortByDesc('date')->values();
    }
}
