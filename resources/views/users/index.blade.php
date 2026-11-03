<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">Pengguna</h2>
                <p class="text-muted mb-0">Kelola akun admin dan kasir.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn crud-create-btn">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        @include('partials.alerts')
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="filter-bar mb-4">
                    <div class="filter-search">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau email">
                    </div>
                    <div class="filter-select">
                        <select name="role" class="form-select">
                            <option value="">Semua Role</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        </select>
                    </div>
                    <div class="filter-select">
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>A - Z</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                    <div class="filter-action">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0" id="usersTable">
                        <thead class="text-secondary" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Telepon</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td data-label="Nama">{{ $user->name }}</td>
                                    <td data-label="Email">{{ $user->email }}</td>
                                    <td data-label="Role">{{ ucfirst($user->role) }}</td>
                                    <td data-label="Telepon">{{ $user->phone }}</td>
                                    <td class="text-end">
                                        <div class="crud-actions" role="group" aria-label="Aksi pengguna">
                                        <a href="{{ route('users.show', $user) }}" class="btn bg-secondary bg-opacity-10 text-secondary border-0 rounded-circle crud-action-btn" title="Lihat detail" aria-label="Lihat detail pengguna"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn bg-primary bg-opacity-10 text-primary border-0 rounded-circle crud-action-btn" title="Edit" aria-label="Edit pengguna"><i class="bi bi-pencil-square"></i></a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn bg-danger bg-opacity-10 text-danger border-0 rounded-circle crud-action-btn" title="Hapus" aria-label="Hapus pengguna"><i class="bi bi-trash"></i></button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $users->links() }}
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
