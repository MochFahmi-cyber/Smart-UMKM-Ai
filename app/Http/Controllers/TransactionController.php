<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exports\TransactionHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::where('store_id', auth()->user()->store_id)
            ->with(['user', 'customer'])
            ->when($request->q, function ($query, $q) {
                $query->where(function($qBuilder) use ($q) {
                    $qBuilder->where('invoice', 'like', "%{$q}%")
                             ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($request->payment_method, function($query, $method) {
                $query->where('payment_method', $method);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'details.product']);

        return view('transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Riwayat transaksi berhasil dihapus.');
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:pending,pending_approval,awaiting_payment,payment_review,completed,rejected'
        ]);

        $transaction->update(['status' => $request->status]);

        return back()->with('success', 'Status transaksi berhasil diperbarui.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new TransactionHistoryExport($request), 'riwayat_transaksi_'.date('YmdHis').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $transactions = Transaction::where('store_id', auth()->user()->store_id)
            ->with(['user', 'customer'])
            ->when($request->q, function ($query, $q) {
                $query->where(function($qBuilder) use ($q) {
                    $qBuilder->where('invoice', 'like', "%{$q}%")
                             ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($request->payment_method, function($query, $method) {
                $query->where('payment_method', $method);
            })
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('transactions.pdf', compact('transactions'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->download('riwayat_transaksi_'.date('YmdHis').'.pdf');
    }

    public function print(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'details.product']);
        return view('transactions.print', compact('transaction'));
    }
}
