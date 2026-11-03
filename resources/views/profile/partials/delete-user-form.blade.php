<section>
    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1 text-danger">Hapus Akun</h2>
            <p class="text-muted small mb-0">
                Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen.
            </p>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Bahaya
        </span>
    </div>

    <div class="alert alert-danger d-flex align-items-center gap-3">
        <i class="bi bi-shield-exclamation fs-4"></i>
        <p class="small mb-0">Tindakan ini bersifat permanen. Pastikan Anda benar-benar ingin melanjutkan.</p>
    </div>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        <i class="bi bi-trash3 me-1"></i> Hapus Akun
    </button>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title h5" id="confirmUserDeletionLabel">
                            Anda yakin ingin menghapus akun?
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <p class="text-muted small mb-3">
                            Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Silakan masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini.
                        </p>

                        <div class="mb-3">
                            <label for="delete_account_password" class="form-label">Password</label>
                            <input id="delete_account_password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Password" required autocomplete="current-password">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash3 me-1"></i> Hapus Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmUserDeletion')).show();
            });
        </script>
    @endif
</section>
