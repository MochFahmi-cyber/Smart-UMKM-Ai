<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 20px;
            width: 80mm;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 2px 0;
        }
        .item-name {
            display: block;
            margin-bottom: 2px;
        }
        .item-qty-price {
            display: inline-block;
        }
        .no-print {
            display: none;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="text-center mb-2">
        <h2 style="margin: 0;">{{ strtoupper($transaction->store->name ?? 'SMART UMKM') }}</h2>
        <p style="margin: 5px 0;">Sistem Kasir Pintar</p>
    </div>

    <div class="divider"></div>

    <div>
        <table style="width: 100%; font-size: 12px;">
            <tr>
                <td>Tgl</td>
                <td>: {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Inv</td>
                <td>: {{ $transaction->invoice }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ $transaction->user->name }}</td>
            </tr>
            <tr>
                <td>Plg</td>
                <td>: {{ $transaction->customer->name ?? 'Umum' }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <table>
        @foreach($transaction->details as $detail)
        <tr>
            <td colspan="3">
                <span class="item-name">{{ $detail->product->name }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 15%;">{{ $detail->quantity }}x</td>
            <td style="width: 40%;">{{ number_format($detail->price, 0, ',', '.') }}</td>
            <td class="text-right" style="width: 45%;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="fw-bold">Total</td>
            <td class="fw-bold text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibayar</td>
            <td class="text-right">Rp {{ number_format($transaction->paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp {{ number_format($transaction->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($transaction->payment_method !== 'cash')
    <div class="divider"></div>
    <div class="text-center" style="font-size: 12px;">
        <p style="margin: 5px 0;">Metode: {{ strtoupper($transaction->payment_method) }}</p>
    </div>
    @endif

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 15px;">
        <p style="margin: 5px 0;">Terima Kasih</p>
        <p style="margin: 5px 0; font-size: 11px;">Powered by Smart UMKM AI</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px;">Cetak Ulang</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px;">Tutup</button>
    </div>

</body>
</html>
