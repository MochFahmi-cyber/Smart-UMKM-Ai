<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->invoice }} - Smart UMKM AI</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f5f9;
            color: #172033;
            font-family: Arial, sans-serif;
        }
        .receipt-page { width: min(100% - 32px, 720px); margin: 32px auto; }
        .receipt-actions { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .button {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #1e3a8a;
            cursor: pointer;
            font-size: 14px;
            padding: 10px 14px;
            text-decoration: none;
        }
        .button--primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .receipt { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); }
        .receipt-header { display: flex; justify-content: space-between; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
        .receipt-title { margin: 0 0 4px; font-size: 20px; }
        .receipt-subtitle, .receipt-meta { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
        .receipt-meta { text-align: right; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 14px; }
        th, td { padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
        th { text-align: left; color: #475569; font-size: 12px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .summary { width: min(100%, 280px); margin-left: auto; }
        .summary-row { display: flex; justify-content: space-between; gap: 24px; padding: 7px 0; }
        .summary-row--total { border-top: 1px solid #cbd5e1; margin-top: 6px; padding-top: 12px; font-size: 16px; }
        .qris-section { margin: 28px auto 0; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; }
        .qris-image { width: min(100%, 220px); border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px; }
        .receipt-footer { margin: 28px 0 0; padding-top: 16px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 13px; text-align: center; }
        @media (max-width: 560px) {
            .receipt-page { width: min(100% - 24px, 720px); margin: 12px auto; }
            .receipt { padding: 20px; }
            .receipt-header { display: block; }
            .receipt-meta { margin-top: 14px; text-align: left; }
        }
        @media print {
            @page { margin: 12mm; size: auto; }
            body { min-height: 0; background: #fff; }
            .receipt-page { width: 100%; margin: 0; }
            .receipt-actions { display: none; }
            .receipt { border: 0; border-radius: 0; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <main class="receipt-page">
        <div class="receipt-actions">
            <a href="{{ route('pos.index') }}" class="button">Kembali ke Kasir</a>
            <button type="button" onclick="window.print()" class="button button--primary">Cetak Struk</button>
        </div>

        <section class="receipt">
            <header class="receipt-header">
                <div>
                    <h1 class="receipt-title">{{ $transaction->store->name ?? 'Smart UMKM AI' }}</h1>
                    <p class="receipt-subtitle">Struk Transaksi</p>
                </div>
                <div class="receipt-meta">
                    <div><strong>Nomor transaksi:</strong> {{ $transaction->invoice }}</div>
                    <div><strong>Tanggal:</strong> {{ $transaction->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                    <div><strong>Kasir:</strong> {{ $transaction->user->name }}</div>
                </div>
            </header>

            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->details as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td class="text-right">{{ $detail->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-row summary-row--total"><span>Total</span><strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong></div>
                <div class="summary-row"><span>Metode bayar</span><strong>{{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</strong></div>
                <div class="summary-row"><span>Dibayar</span><strong>Rp {{ number_format($transaction->paid, 0, ',', '.') }}</strong></div>
                <div class="summary-row"><span>Kembalian</span><strong>Rp {{ number_format($transaction->change, 0, ',', '.') }}</strong></div>
            </div>

            @if($transaction->payment_method === 'qris' && $transaction->store && $transaction->store->qris_image)
                <div class="qris-section">
                    <strong>Kode QRIS Pembayaran</strong><br>
                    <img src="{{ asset('storage/' . $transaction->store->qris_image) }}" alt="Kode QRIS transaksi {{ $transaction->invoice }}" class="qris-image">
                </div>
            @endif

            <p class="receipt-footer">Terima kasih telah berbelanja.</p>
        </section>
    </main>
</body>
</html>
