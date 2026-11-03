<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Pelanggan</h2>
                <p class="text-muted mb-0">Kelola daftar pelanggan Anda.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn crud-create-btn">
                <i class="bi bi-plus-lg"></i> Tambah Pelanggan
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="filter-bar mb-4">
                    <div class="filter-search">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, email, atau telepon">
                    </div>
                    <div class="filter-select">
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>A - Z</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                    <div class="filter-action">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0" id="customersTable">
                        <thead class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td data-label="Nama">{{ $customer->name }}</td>
                                    <td data-label="Email">{{ $customer->email }}</td>
                                    <td data-label="Telepon">{{ $customer->phone }}</td>
                                    <td data-label="Alamat">{{ Str::limit($customer->address, 70) }}</td>
                                    <td class="text-end">
                                        <div class="crud-actions" role="group" aria-label="Aksi pelanggan">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn bg-secondary bg-opacity-10 text-secondary border-0 rounded-circle crud-action-btn" title="Lihat detail" aria-label="Lihat detail pelanggan"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn bg-primary bg-opacity-10 text-primary border-0 rounded-circle crud-action-btn" title="Edit" aria-label="Edit pelanggan"><i class="bi bi-pencil-square"></i></a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus pelanggan ini?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger bg-opacity-10 text-danger border-0 rounded-circle crud-action-btn" title="Hapus" aria-label="Hapus pelanggan"><i class="bi bi-trash"></i></button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $customers->links() }}
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
