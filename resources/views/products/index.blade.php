<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Produk</h2>
                <p class="text-muted mb-0">Kelola produk yang tersedia di POS.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn crud-create-btn">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="filter-bar mb-4">
                    <div class="filter-search">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau SKU produk">
                    </div>
                    <div class="filter-select">
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-select">
                        <select name="status" class="form-select">
                            <option value="">Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="filter-select">
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>A - Z</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                    <div class="filter-action">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0" id="productsTable">
                        <thead class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>SKU</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td data-label="Nama">{{ $product->name }}</td>
                                    <td data-label="Kategori">{{ $product->category->name ?? '-' }}</td>
                                    <td data-label="SKU">{{ $product->sku }}</td>
                                    <td data-label="Harga Jual">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>
                                    <td data-label="Stok">{{ $product->stock }}</td>
                                    <td data-label="Status">
                                        <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">{{ $product->active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="crud-actions" role="group" aria-label="Aksi produk">
                                        <a href="{{ route('products.show', $product) }}" class="btn bg-secondary bg-opacity-10 text-secondary border-0 rounded-circle crud-action-btn" title="Lihat detail" aria-label="Lihat detail produk"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn bg-primary bg-opacity-10 text-primary border-0 rounded-circle crud-action-btn" title="Edit" aria-label="Edit produk"><i class="bi bi-pencil-square"></i></a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger bg-opacity-10 text-danger border-0 rounded-circle crud-action-btn" title="Hapus" aria-label="Hapus produk"><i class="bi bi-trash"></i></button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $products->links() }}
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
