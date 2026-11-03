<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="h4 mb-1">Detail Transaksi</h2>
            <p class="text-muted mb-0">Informasi rinci transaksi.</p>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-1 fw-bold text-primary">Invoice: {{ $transaction->invoice }}</h5>
                        <p class="text-muted mb-0">Tanggal: {{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>



                <div class="row mt-4 pt-2 border-top">
                    <div class="col-12">
                        <div class="bg-light p-4 rounded shadow-sm border">
                            
                            <!-- Daftar Pesanan (Struk) -->
                            <h6 class="fw-bold mb-3 mt-1 text-dark">Daftar Pesanan</h6>
                            @foreach($transaction->details as $detail)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-dark">{{ $detail->product->name }}</span>
                                        <span class="fw-semibold text-dark">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                            
                            <hr class="border-secondary opacity-25 my-4">

                            <!-- Ringkasan Total -->
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Jumlah Item</span>
                                <span class="fw-medium">{{ $transaction->items_count }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Belanja</span>
                                <span class="fw-bold">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between align-items-center text-success">
                                <span class="text-success">Dibayar</span>
                                <span class="fw-bold">Rp {{ number_format($transaction->paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-6">Kembalian</span>
                                <span class="fw-bold fs-6">Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
                            </div>

                            @if($transaction->notes)
                            <div class="mb-4 bg-white p-3 rounded border border-warning border-opacity-50">
                                <span class="text-muted d-block fw-semibold mb-1"><i class="bi bi-card-text me-1"></i> Catatan Pesanan:</span>
                                <span>{{ $transaction->notes }}</span>
                            </div>
                            @endif
                            
                            <hr class="border-secondary opacity-25">

                            <!-- Informasi Pelanggan -->
                            <h6 class="fw-bold mb-3 mt-1 text-dark">Informasi Pelanggan</h6>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Nama</span>
                                <span class="text-end fw-medium">{{ $transaction->customer->name ?? 'Umum' }}</span>
                            </div>
                            @if($transaction->customer && $transaction->customer->phone)
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">No HP</span>
                                <span class="text-end">{{ $transaction->customer->phone }}</span>
                            </div>
                            @endif
                            @if($transaction->customer && $transaction->customer->address)
                            <div class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">Alamat</span>
                                <span class="text-end" style="max-width: 65%;">{{ $transaction->customer->address }}</span>
                            </div>
                            @endif
                            
                            <!-- Kasir (Kanan Bawah) -->
                            <div class="mt-4 pt-3 border-top text-end text-muted small">
                                <i class="bi bi-person-badge"></i> Dilayani oleh Kasir: <strong class="text-dark">{{ $transaction->user->name }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if($transaction->payment_proof)
                    <div class="mt-4 pt-4 border-top text-center">
                        <h6 class="mb-3 fw-bold text-success"><i class="bi bi-receipt me-2"></i>Bukti Pembayaran</h6>
                        <a href="{{ asset('storage/' . $transaction->payment_proof) }}" target="_blank">
                            <img src="{{ asset('storage/' . $transaction->payment_proof) }}" alt="Bukti Pembayaran" class="img-fluid border rounded p-2 shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </a>
                        <p class="text-muted small mt-2">Klik gambar untuk memperbesar</p>
                    </div>
                @elseif($transaction->payment_method === 'qris' && $transaction->store && $transaction->store->qris_image)
                    <div class="mt-4 pt-4 border-top text-center">
                        <h6 class="mb-3 text-muted">Kode QRIS Toko</h6>
                        <img src="{{ asset('storage/' . $transaction->store->qris_image) }}" alt="Kode QRIS Toko" class="img-fluid border rounded p-2 opacity-75" style="max-width: 200px;">
                    </div>
                @endif

            </div>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary px-4 rounded-pill shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</x-app-layout>
