<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return Transaction::where('user_id', Auth::id())
            ->with(['user', 'customer'])
            ->when($this->request->q, function ($query, $q) {
                $query->where(function($qBuilder) use ($q) {
                    $qBuilder->where('invoice', 'like', "%{$q}%")
                             ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($this->request->payment_method, function($query, $method) {
                $query->where('payment_method', $method);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Pelanggan',
            'Kasir',
            'Metode Pembayaran',
            'Sumber',
            'Status',
            'Total Belanja',
            'Dibayar',
            'Kembalian',
            'Tanggal',
        ];
    }

    public function map($transaction): array
    {
        $sumber = Str::startsWith($transaction->invoice, 'ORD-') ? 'Customer QR' : 'Kasir';
        
        $statusText = match($transaction->status) {
            'pending_approval' => 'Menunggu Persetujuan',
            'awaiting_payment' => 'Menunggu Pembayaran',
            'payment_review' => 'Cek Pembayaran',
            'completed' => 'Lunas / Selesai',
            'rejected' => 'Ditolak',
            'pending' => 'Pending',
            default => ucfirst($transaction->status)
        };

        return [
            $transaction->invoice,
            $transaction->customer->name ?? 'Umum',
            $transaction->user->name,
            strtoupper($transaction->payment_method),
            $sumber,
            $statusText,
            $transaction->total,
            $transaction->paid,
            $transaction->change,
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
