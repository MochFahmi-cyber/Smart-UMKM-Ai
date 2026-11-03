<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Riwayat Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0 0; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Riwayat Transaksi</h2>
        <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Kasir</th>
                <th>Sumber</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $transaction->invoice }}</td>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->customer->name ?? 'Umum' }}</td>
                <td>{{ $transaction->user->name }}</td>
                <td>{{ \Illuminate\Support\Str::startsWith($transaction->invoice, 'ORD-') ? 'Customer QR' : 'Kasir' }}</td>
                <td>
                    @php
                        $statusText = match($transaction->status) {
                            'pending_approval' => 'Menunggu Persetujuan',
                            'awaiting_payment' => 'Menunggu Pembayaran',
                            'payment_review' => 'Cek Pembayaran',
                            'completed' => 'Lunas / Selesai',
                            'rejected' => 'Ditolak',
                            'pending' => 'Pending',
                            default => ucfirst($transaction->status)
                        };
                    @endphp
                    {{ $statusText }}
                </td>
                <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
