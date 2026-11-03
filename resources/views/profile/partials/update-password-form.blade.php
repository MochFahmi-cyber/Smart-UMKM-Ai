<section>
    <header class="d-flex align-items-start gap-3 mb-4">
        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
            <i class="bi bi-shield-lock-fill fs-4"></i>
        </div>
        <div>
            <h2 class="h5 mb-1">Keamanan Akun</h2>
            <p class="text-muted small mb-0">Gunakan password yang kuat untuk menjaga keamanan akun Anda.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Password Saat Ini</label>
            <input type="password" id="update_password_current_password" name="current_password" class="form-control" autocomplete="current-password" placeholder="Masukkan password lama Anda">
            @error('current_password', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">Password Baru</label>
            <input type="password" id="update_password_password" name="password" class="form-control" autocomplete="new-password" placeholder="Masukkan password baru Anda">
            @error('password', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="update_password_password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Ketik ulang password baru">
            @error('password_confirmation', 'updatePassword')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-lock me-1"></i> Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-success small mb-0 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Password berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
