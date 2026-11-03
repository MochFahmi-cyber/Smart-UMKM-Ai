@extends('layouts.customer')

@section('content')
<div class="container-fluid px-2">
    
    <!-- Store Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem;">
                {{ substr($token->user->name, 0, 1) }}
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-1">{{ $token->user->name }}</h5>
                <p class="text-primary mb-0 small fw-semibold"><i class="bi bi-shop me-1"></i> Selamat Berbelanja!</p>
            </div>
        </div>
    </div>

    <!-- Active Transactions Banner -->
    @if(isset($activeTransactions) && count($activeTransactions) > 0)
    <div class="mb-4">
        @foreach($activeTransactions as $tx)
        <div class="alert alert-info shadow-sm rounded-4 d-flex align-items-center justify-content-between mb-2 border-0" style="background-color: #f8fafc;">
            <div>
                <div class="fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Pesanan {{ $tx->invoice }}</div>
                <div class="small text-muted mt-1">Status: <span class="badge bg-warning text-dark">{{ str_replace('_', ' ', $tx->status) }}</span></div>
            </div>
            <a href="{{ route('customer-kasir.success', ['token' => $token->token, 'invoice' => $tx->invoice]) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">Cek Detail</a>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Search & Filter -->
    <div class="mb-4">
        <form method="GET" action="{{ route('customer-kasir.index', $token->token) }}" class="mb-3">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                <span class="input-group-text bg-white border-0 text-muted ps-4"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-0 shadow-none py-2" placeholder="Cari produk kesukaanmu..." value="{{ request('search') }}">
                <button class="btn btn-primary px-4 fw-bold" type="submit">Cari</button>
            </div>
        </form>
        <div class="d-flex gap-2 overflow-auto pb-2 category-scroll" style="white-space: nowrap; -webkit-overflow-scrolling: touch;">
            <a href="{{ route('customer-kasir.index', $token->token) }}" class="btn btn-sm rounded-pill px-3 fw-medium flex-shrink-0 shadow-sm {{ !request('category') ? 'btn-primary' : 'btn-white border text-dark' }}">Semua</a>
            @foreach($categories as $cat)
                <a href="{{ route('customer-kasir.index', ['token' => $token->token, 'category' => $cat->id]) }}" class="btn btn-sm rounded-pill px-3 fw-medium flex-shrink-0 shadow-sm {{ request('category') == $cat->id ? 'btn-primary' : 'btn-white border text-dark' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-3">
        @forelse($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card shadow-sm border h-100 rounded-4 transition-hover bg-white d-flex flex-column">
                    <div class="p-3 d-flex flex-column h-100">
                        <div class="product-name fw-bold mb-1 text-dark" style="font-size: 0.95rem; line-height: 1.4;">{{ $product->name }}</div>
                        <div class="product-price mb-3 text-primary fw-bold" style="font-size: 1.1rem;">Rp{{ number_format($product->sell_price, 0, ',', '.') }}</div>
                        
                        <div class="mt-auto">
                            @if($product->stock > 0)
                                <form action="{{ route('customer-kasir.cart.add', $token->token) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-primary w-100 btn-add-cart py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-cart-plus me-1"></i> Tambah
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-light border w-100 btn-add-cart py-2 text-danger fw-semibold" disabled>
                                    Stok Habis
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="bg-white rounded-4 shadow-sm p-5 text-muted">
                    <i class="bi bi-box-seam fs-1 d-block mb-3 text-primary opacity-75"></i>
                    <h5 class="fw-bold text-dark">Katalog Kosong</h5>
                    <p class="mb-0">Produk yang Anda cari tidak ditemukan.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>

<!-- Cart Offcanvas -->
<div class="offcanvas offcanvas-bottom rounded-top-4 shadow" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="height: 85vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="cartOffcanvasLabel">Keranjang Belanja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <div class="flex-grow-1 overflow-auto p-3">
            @php $total = 0; @endphp
            @forelse($cart as $id => $item)
                @php $total += $item['price'] * $item['quantity']; @endphp
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">{{ $item['name'] }}</h6>
                        <div class="text-primary fw-bold">Rp{{ number_format($item['price'], 0, ',', '.') }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Decrease -->
                        <form action="{{ route('customer-kasir.cart.update', ['token' => $token->token, 'product_id' => $id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="decrease">
                            <button type="submit" class="btn btn-light border cart-qty-btn"><i class="bi bi-dash"></i></button>
                        </form>
                        
                        <span class="fw-bold px-2">{{ $item['quantity'] }}</span>

                        <!-- Increase -->
                        <form action="{{ route('customer-kasir.cart.update', ['token' => $token->token, 'product_id' => $id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="increase">
                            <button type="submit" class="btn btn-light border cart-qty-btn"><i class="bi bi-plus"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Keranjang Anda masih kosong
                </div>
            @endforelse
        </div>
        
        <div class="p-3 bg-light border-top">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold">Total Pembayaran</span>
                <span class="fw-bold fs-5 text-primary">Rp{{ number_format($total, 0, ',', '.') }}</span>
            </div>
            
            <button type="button" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#checkoutModal" {{ count($cart) == 0 ? 'disabled' : '' }}>
                Checkout Sekarang
            </button>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('customer-kasir.checkout', $token->token) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="checkoutModalLabel">Lengkapi Data Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select" id="paymentMethodSelect" required onchange="toggleAddressRequire()">
                        @if($token->store->qris_active)
                            <option value="qris">QRIS</option>
                        @endif
                        @if($token->store->bank_account)
                            <option value="transfer">Transfer Bank</option>
                        @endif
                        <option value="cash">Tunai (Bayar Langsung)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama Anda" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control" placeholder="Contoh: 081234567890" required>
                </div>

                <div class="mb-3" id="addressContainer" style="display:none;">
                    <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="address" id="addressInput" class="form-control" rows="3" placeholder="Masukkan alamat lengkap untuk pengiriman... (Contoh: Blok Kamis RT 02/RW 01, Desa Sindang, rumah warna putih)"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Pesanan (Opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Tolong dikirim setelah jam 5 sore.">
                </div>

            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                    Kirim Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

@if(count($cart ?? []) > 0)
<!-- Sticky bottom cart bar to open offcanvas -->
<div class="cart-bar d-flex justify-content-between align-items-center d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" role="button">
    <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
            <i class="bi bi-cart-fill fs-3 text-primary"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ collect($cart ?? [])->sum('quantity') }}
            </span>
        </div>
        <div>
            <div class="small text-muted mb-0 lh-1">Total Pembayaran</div>
            <div class="fw-bold text-dark mb-0">Rp{{ number_format($total, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="fw-bold text-primary">
        Checkout <i class="bi bi-chevron-right"></i>
    </div>
</div>
@endif

@push('scripts')
<script>
    function toggleAddressRequire() {
        const select = document.getElementById('paymentMethodSelect');
        const addressInput = document.getElementById('addressInput');
        const addressContainer = document.getElementById('addressContainer');
        
        if (select.value === 'qris' || select.value === 'transfer') {
            addressContainer.style.display = 'block';
            addressInput.required = true;
        } else {
            addressContainer.style.display = 'none';
            addressInput.required = false;
        }
    }
    
    // Call once on load to set initial state
    document.addEventListener('DOMContentLoaded', toggleAddressRequire);
</script>
@endpush

@endsection
