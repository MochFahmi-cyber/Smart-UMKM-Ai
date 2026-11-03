<x-app-layout>

<x-slot name="header">

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

<div>

<h2 class="fw-bold mb-1">
<i class="bi bi-cart-check text-primary me-2"></i>
Kasir Pintar
</h2>

<p class="text-muted mb-0">
Kelola transaksi UMKM lebih cepat dengan Smart UMKM AI.
</p>

</div>


<div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill align-self-start align-self-sm-auto">

<i class="bi bi-lightning-charge me-1"></i>
Point Of Sale

</div>


</div>

</x-slot>



<div class="container-fluid">


@include('partials.alerts')



<div class="row g-4 pos-layout">



<!-- PRODUK -->
@php $hasCart = count($cart) > 0; @endphp
<div class="col-12 {{ $hasCart ? 'col-lg-5 col-xl-5 order-2 order-lg-1' : '' }} pos-products-column" style="animation: fadeIn 0.4s ease-out;">

<div class="card pos-card pos-product-panel">


<div class="card-header bg-transparent border-0 pt-4 px-4">


<div class="d-flex justify-content-between align-items-center">


<h5 class="fw-bold mb-0">

<i class="bi bi-box-seam text-primary me-2"></i>

Produk

</h5>


<span class="badge bg-success bg-opacity-10 text-success">

{{ count($products) }} Item

</span>


</div>



<div class="mt-3">
    <form method="GET" action="{{ route('pos.index') }}" class="d-flex flex-column flex-sm-row gap-2">
        <select name="category" class="form-select bg-body-secondary border-0 flex-shrink-0" style="width: auto;" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <div class="input-group">
            <span class="input-group-text bg-body-secondary border-0">
                <i class="bi bi-search"></i>
            </span>
            <input 
                type="text"
                id="searchProduct"
                class="form-control bg-body-secondary border-0"
                placeholder="Cari produk di kategori ini...">
        </div>
    </form>
</div>


</div>





<div class="card-body px-4 pos-product-body">


<div class="row pos-product-grid g-3" id="productList">



@foreach($products as $product)

<div class="col-6 col-sm-4 {{ $hasCart ? 'col-md-4 col-lg-6 col-xl-6' : 'col-md-3 col-lg-2 col-xl-2' }} product-item">
    <div class="card product-card h-100 w-100 p-2 text-center position-relative shadow-sm border-0">

        <div class="position-absolute top-0 end-0 p-1">
            <span class="badge {{ $product->stock > 5 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} rounded-pill" style="font-size: 0.65rem;">
                {{ $product->stock }} Stok
            </span>
        </div>

        <div class="text-center mb-2 flex-grow-1 mt-4 px-1">
            <h6 class="product-name fw-bold mb-1 text-dark" style="font-size: 0.85rem; line-height: 1.2;">{{ $product->name }}</h6>
            <div class="text-muted mb-1" style="font-size: 0.7rem;">{{ $product->sku }}</div>
            <div class="mb-1">
                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 0.65rem;">
                    {{ $product->category->name ?? 'Tanpa Kategori' }}
                </span>
            </div>
            <div class="text-primary fw-bold" style="font-size: 0.95rem;">Rp {{ number_format($product->sell_price,0,',','.') }}</div>
        </div>

        <form action="{{ route('pos.cart.add') }}" method="POST" class="mt-auto">

    @csrf

    <input
        type="hidden"
        name="product_id"
        value="{{ $product->id }}">

    <input
        type="hidden"
        name="quantity"
        value="1">

    <button
        class="btn btn-primary w-100 py-2 rounded-3 btn-sm"
        @disabled($product->stock < 1)>

        <i class="bi bi-cart-plus me-1"></i>
        {{ $product->stock < 1 ? 'Stok Habis' : 'Tambah' }}

    </button>

</form>

    </div>

</div>


@endforeach



</div>


</div>


</div>


</div>







<!-- KERANJANG -->
@if($hasCart)
<div class="col-12 col-lg-7 col-xl-7 order-1 order-lg-2 slide-in-right">


<div class="card pos-card">


<div class="card-header bg-transparent border-0 pt-4 px-4">


<h5 class="fw-bold">

<i class="bi bi-basket text-success me-2"></i>

Keranjang Belanja
@php $totalQty = collect($cart)->sum('quantity'); @endphp
@if($totalQty > 0)
<span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.75rem;">{{ $totalQty }}</span>
@endif

</h5>


</div>




<div class="card-body px-4">


<div class="table-responsive">


<table class="table align-middle">


<thead>


<tr>

<th>Produk</th>

<th>Harga</th>

<th>Qty</th>

<th>Total</th>

<th></th>

</tr>


</thead>



<tbody>


@php $total=0; @endphp


@forelse($cart as $item)


@php $total += $item['subtotal']; @endphp


<tr>


<td class="fw-semibold">

{{ $item['name'] }}

</td>



<td>

Rp {{ number_format($item['price'],0,',','.') }}

</td>




<td>


<form action="{{ route('pos.cart.update',$item['id']) }}" method="POST">
    @csrf
    @method('PATCH')
    <div class="input-group input-group-sm" style="width: 100px;">
        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control text-center" aria-label="Qty">
        <button class="btn btn-outline-primary" type="submit" title="Update Qty">
            <i class="bi bi-arrow-repeat"></i>
        </button>
    </div>
</form>


</td>




<td class="fw-bold text-primary">

Rp {{ number_format($item['subtotal'],0,',','.') }}

</td>




<td>


<form action="{{ route('pos.cart.remove',$item['id']) }}"
method="POST">

@csrf

@method('DELETE')


<button class="btn btn-outline-danger btn-sm">

<i class="bi bi-trash"></i>

</button>


</form>


</td>


</tr>


@empty


<tr>

<td colspan="5"
class="text-center text-muted py-4">

Keranjang masih kosong

</td>

</tr>


@endforelse



</tbody>


</table>


</div>






<div class="total-box mt-4">


<span>Total Pembayaran</span>


<h2>

Rp {{ number_format($total,0,',','.') }}

</h2>


</div>






<form action="{{ route('pos.checkout') }}"
method="POST"
enctype="multipart/form-data"
class="mt-4">

@csrf



<label for="customer_id" class="form-label fw-semibold">

Pilih Pelanggan

</label>


<select id="customer_id"
name="customer_id"
class="form-select mb-1">


<option value="">
Pelanggan Umum
</option>


@foreach($customers as $customer)


<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>

{{ $customer->name }}{{ $customer->phone ? ' — '.$customer->phone : '' }}

</option>


@endforeach


</select>

<div class="form-text mb-3">
{{ $customers->isEmpty() ? 'Belum ada pelanggan terdaftar. Transaksi akan dicatat sebagai pelanggan umum.' : 'Daftar diambil dari data pelanggan yang sudah terdaftar.' }}
</div>





<label for="paymentMethod" class="form-label fw-semibold">

Metode Pembayaran

</label>


<select id="paymentMethod"
name="payment_method"
class="form-select mb-3">

<option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Tunai</option>
<option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>

</select>


<div id="qrisPaymentPanel" class="qris-payment-panel mb-3" hidden>

    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
        <img id="qrisPreview" src="{{ asset('images/qris.jpeg') }}" class="qris-preview" alt="Kode QRIS pembayaran">
        <div class="text-center text-sm-start">
            <strong class="d-block">Scan QRIS untuk membayar</strong>
            <small class="text-muted d-block mb-2">Nominal QRIS otomatis sesuai total transaksi.</small>
            <label for="qrisImage" class="btn btn-outline-primary btn-sm mb-0">
                <i class="bi bi-image me-1"></i>Ganti gambar QRIS
            </label>
            <input id="qrisImage" name="qris_image" type="file" accept="image/*" class="d-none">
            <small id="qrisFileName" class="d-block text-muted mt-1">Menggunakan QRIS default.</small>
        </div>
    </div>

</div>


<label for="paidAmount" class="form-label fw-semibold">

Jumlah Uang

</label>


<input 
type="number"
id="paidAmount"
name="paid"
class="form-control mb-3"
value="{{ old('paid',$total) }}">





<label for="notes" class="form-label fw-semibold">

Catatan

</label>


<textarea 
id="notes"
name="notes"
class="form-control mb-3"></textarea>




<label for="changeAmount" class="form-label fw-semibold">

Kembalian

</label>


<input 
id="changeAmount"
class="form-control fw-bold mb-4"
readonly
value="Rp 0">



<button class="btn btn-success btn-lg w-100">

<i class="bi bi-check-circle me-2"></i>

Bayar Transaksi

</button>



</form>



</div>

</div>


</div>



</div>
@endif
</div>


</div>





@push('styles')

<style>


.pos-card{
border:0;
border-radius:22px;
box-shadow:0 15px 40px rgba(15,23,42,.08);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.slide-in-right {
    animation: slideInRight 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

@keyframes slideInRight {
    0% { transform: translateX(30px); opacity: 0; }
    100% { transform: translateX(0); opacity: 1; }
}

/* Daftar produk tetap ringkas meski jumlah produk bertambah banyak. */
.pos-product-panel {
    display: flex;
    flex-direction: column;
    height: min(44rem, calc(100dvh - 2rem));
}

.pos-product-body {
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
}

.pos-product-grid {
    --bs-gutter-x: .75rem;
    --bs-gutter-y: .75rem;
}

.pos-product-grid .product-item {
    display: flex;
}




.product-card{

background:var(--bs-tertiary-bg);

border:1px solid var(--bs-border-color);

padding:18px;

border-radius:18px;

transition:.25s;

}



.product-card:hover{

transform:translateY(-5px);

box-shadow:0 10px 25px rgba(0,0,0,.08);

border-color:#2563eb;

}



.total-box{

background:linear-gradient(135deg,#2563eb,#06b6d4);

color:white;

padding:25px;

border-radius:20px;

}



.total-box h2{

font-weight:800;

margin:5px 0 0;

}

/* Responsif untuk kartu produk */
.product-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-card .btn-add-cart {
    margin-top: auto;
}

.product-name {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    min-height: 3.6em;
    margin-bottom: 0.2rem;
    word-break: normal;
}

.qris-payment-panel {
    padding: 1rem;
    border: 1px solid #bfdbfe;
    border-radius: 14px;
    background: #eff6ff;
}

.qris-preview {
    width: 150px;
    height: 150px;
    object-fit: contain;
    border-radius: 10px;
    background: #fff;
    padding: .4rem;
}

@media (max-width: 575.98px) {
    .pos-product-panel {
        height: min(34rem, 58dvh);
    }

    .pos-product-panel .card-header,
    .pos-product-panel .card-body,
    .pos-card .card-header,
    .pos-card .card-body {
        padding-right: 1rem !important;
        padding-left: 1rem !important;
    }

    .product-card {
        padding: 12px;
        border-radius: 14px;
    }
    .product-icon {
        font-size: 1.4rem;
    }
    .product-name {
        font-size: 0.85rem;
    }
    .product-sku {
        font-size: 0.7rem;
    }
    .product-price {
        font-size: 0.85rem;
    }
    .product-card .btn-add-cart {
        font-size: 0.8rem;
        padding: 0.4rem 0.5rem;
    }
}

@media (min-width: 1200px) {
    .pos-product-panel {
        position: sticky;
        top: 1rem;
    }
}

</style>

@endpush





@push('scripts')


<script>


document.getElementById('searchProduct')
.addEventListener('input',function(){


let value=this.value.toLowerCase();


document.querySelectorAll('.product-item')
.forEach(function(item){


let name=item.querySelector('.product-name')
.innerText.toLowerCase();



item.style.display=
name.includes(value)
?''
:'none';


});


});





const totalAmount={{ $total ?? 0 }};

const paid=document.querySelector('input[name="paid"]');

const change=document.getElementById('changeAmount');
const paymentMethod=document.getElementById('paymentMethod');
const qrisPanel=document.getElementById('qrisPaymentPanel');
const qrisImage=document.getElementById('qrisImage');
const qrisPreview=document.getElementById('qrisPreview');
const qrisFileName=document.getElementById('qrisFileName');

function updatePaymentMethod() {
    const isQris=paymentMethod.value === 'qris';
    qrisPanel.hidden=!isQris;
    paid.readOnly=isQris;

    if (isQris) {
        paid.value=totalAmount;
    }

    const result=paid.value-totalAmount;
    change.value='Rp '+Math.max(result,0).toLocaleString('id-ID');
}

paid.addEventListener('input',()=>{


let result=paid.value-totalAmount;


change.value=
'Rp '+Math.max(result,0)
.toLocaleString('id-ID');


});

paymentMethod.addEventListener('change', updatePaymentMethod);

qrisImage.addEventListener('change', function () {
    const file=this.files[0];
    if (!file) return;
    qrisPreview.src=URL.createObjectURL(file);
    qrisFileName.textContent=file.name;
});

updatePaymentMethod();



</script>


@endpush



</x-app-layout>
