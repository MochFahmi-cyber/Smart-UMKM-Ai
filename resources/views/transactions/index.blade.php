<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Transaksi</h2>
                <p class="text-muted mb-0">Riwayat transaksi kasir.</p>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="filter-bar mb-4">
                    <div class="filter-search">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari invoice atau pelanggan">
                    </div>
                    <div class="filter-select">
                        <select name="payment_method" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                    </div>
                    <div class="filter-action d-flex gap-2">
                        <button type="submit" class="btn btn-primary" title="Cari"><i class="bi bi-search"></i></button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                        <a href="{{ route('transactions.export.excel', request()->all()) }}" class="btn btn-success" title="Export Excel">
                            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-sm-inline ms-1">Excel</span>
                        </a>
                        <a href="{{ route('transactions.export.pdf', request()->all()) }}" class="btn btn-danger" title="Export PDF">
                            <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline ms-1">PDF</span>
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0" id="transactionsTable">
                        <thead class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th>Sumber</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td data-label="Invoice">{{ $transaction->invoice }}</td>
                                    <td data-label="Pelanggan">
                                        {{ $transaction->customer->name ?? 'Umum' }}
                                        @if($transaction->notes)
                                            <br><small class="text-muted"><i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($transaction->notes, 20) }}</small>
                                        @endif
                                    </td>
                                    <td data-label="Kasir">{{ $transaction->user->name }}</td>
                                    <td data-label="Total">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                    <td data-label="Tanggal">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td data-label="Sumber">
                                        @if(Str::startsWith($transaction->invoice, 'ORD-'))
                                            <span class="badge bg-primary bg-opacity-10 text-primary"><i class="bi bi-qr-code-scan me-1"></i> Customer QR</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-person-workspace me-1"></i> Kasir</span>
                                        @endif
                                    </td>
                                    <td data-label="Status">
                                        @php
                                            $statusClass = match($transaction->status) {
                                                'completed' => 'bg-success text-white',
                                                'rejected' => 'bg-danger text-white',
                                                'pending' => 'bg-secondary text-white',
                                                default => 'bg-warning text-dark'
                                            };
                                            $statusText = match($transaction->status) {
                                                'pending_approval' => 'Menunggu Persetujuan',
                                                'awaiting_payment' => 'Menunggu Pembayaran',
                                                'payment_review' => 'Cek Pembayaran',
                                                default => ucfirst($transaction->status)
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="crud-actions d-inline-flex gap-1" role="group" aria-label="Aksi transaksi">
                                            
                                            <!-- Action Buttons based on status -->
                                            @if($transaction->status == 'pending_approval')
                                                <form action="{{ route('transactions.update-status', $transaction) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="awaiting_payment">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Terima Pesanan"><i class="bi bi-check-lg"></i></button>
                                                </form>
                                                <form action="{{ route('transactions.update-status', $transaction) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Tolak Pesanan"><i class="bi bi-x-lg"></i></button>
                                                </form>
                                            @endif

                                            @if(in_array($transaction->status, ['pending', 'awaiting_payment', 'payment_review']))
                                                <form action="{{ route('transactions.update-status', $transaction) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Tandai Lunas"><i class="bi bi-check-all"></i> Lunas</button>
                                                </form>
                                            @endif

                                            @if($transaction->payment_proof)
                                                <a href="{{ Storage::url($transaction->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat Bukti Pembayaran"><i class="bi bi-receipt"></i></a>
                                            @endif
                                            
                                            <a href="{{ route('transactions.print', $transaction) }}" target="_blank" class="btn bg-primary bg-opacity-10 text-primary border-0 rounded-circle crud-action-btn" title="Cetak Struk" aria-label="Cetak Struk"><i class="bi bi-printer"></i></a>
                                            <a href="{{ route('transactions.show', $transaction) }}" class="btn bg-secondary bg-opacity-10 text-secondary border-0 rounded-circle crud-action-btn" title="Lihat detail" aria-label="Lihat detail transaksi"><i class="bi bi-eye"></i></a>
                                            
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn bg-danger bg-opacity-10 text-danger border-0 rounded-circle crud-action-btn" title="Hapus" aria-label="Hapus transaksi"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('styles')
<style>
.table > :not(caption) > * > * {
    padding: 1rem 1rem;
}
</style>
    @endpush
</x-app-layout>
