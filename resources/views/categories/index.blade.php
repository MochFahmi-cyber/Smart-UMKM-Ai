<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Kategori</h2>
                <p class="text-muted mb-0">Kelola kategori produk.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="btn crud-create-btn">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="filter-bar mb-4">
                    <div class="filter-search">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kategori">
                    </div>
                    <div class="filter-select">
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>A - Z</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                    <div class="filter-action">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0" id="categoriesTable">
                        <thead class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td data-label="Nama">{{ $category->name }}</td>
                                    <td data-label="Deskripsi">{{ Str::limit($category->description, 80) }}</td>
                                    <td class="text-end">
    <div class="crud-actions" role="group" aria-label="Aksi kategori">

        <a href="{{ route('categories.show', $category) }}"
           class="btn bg-secondary bg-opacity-10 text-secondary border-0 rounded-circle crud-action-btn"
           data-bs-toggle="tooltip"
           title="Lihat Detail"
           aria-label="Lihat detail kategori">

            <i class="bi bi-eye"></i>
        </a>

        <a href="{{ route('categories.edit', $category) }}"
           class="btn bg-primary bg-opacity-10 text-primary border-0 rounded-circle crud-action-btn"
           data-bs-toggle="tooltip"
           title="Edit"
           aria-label="Edit kategori">

            <i class="bi bi-pencil-square"></i>
        </a>

        <form action="{{ route('categories.destroy', $category) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Hapus kategori ini?');">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn bg-danger bg-opacity-10 text-danger border-0 rounded-circle crud-action-btn"
                    data-bs-toggle="tooltip"
                    title="Hapus"
                    aria-label="Hapus kategori">

                <i class="bi bi-trash"></i>
            </button>

        </form>

    </div>
</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
<style>

.category-card{
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(15,23,42,.08);
}

.table > :not(caption) > * > * {
    padding: 1rem 1rem;
}

</style>
@endpush


</x-app-layout>
