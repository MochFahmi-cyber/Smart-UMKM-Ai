<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Edit Produk</h2>
                <p class="text-muted mb-0">Perbarui detail produk yang dipilih.</p>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')
        <div class="card shadow-sm">
            <div class="card-body">
<form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" placeholder="Contoh: Kopi Hitam" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" placeholder="Contoh: KPH-01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock) }}" class="form-control" placeholder="Jumlah stok tersedia" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Beli</label>
                            <input type="number" name="purchase_price" min="0" step="0.01" value="{{ old('purchase_price', $product->purchase_price) }}" class="form-control" placeholder="Harga beli/modal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" name="sell_price" min="0" step="0.01" value="{{ old('sell_price', $product->sell_price) }}" class="form-control" placeholder="Harga jual ke pelanggan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="active" class="form-select" required>
                                <option value="1" {{ old('active', $product->active) ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('active', $product->active) ? '' : 'selected' }}>Nonaktif</option>
                            </select>
                        </div>
<div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
