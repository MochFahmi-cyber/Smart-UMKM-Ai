<x-guest-layout class="auth-page auth-page-register">
    <form method="POST" action="{{ route('register') }}" class="auth-register-form">
        @csrf

        <div class="mb-3 text-center d-none d-lg-block">
            <h2 class="h5 fw-bold mb-1">Buat Akun Smart UMKM AI</h2>
            <p class="text-muted mb-0 small">Kelola penjualan, stok, dan analisis bisnis dengan bantuan AI.</p>
        </div>

        <div class="mb-3">
            <h6 class="fw-bold border-bottom pb-2">Data Pemilik</h6>
        </div>

        <div class="mb-3">
            <x-input-label for="name" :value="__('Nama Lengkap')" class="form-label" />
            <x-text-input id="name" class="form-control" type="text" name="name" :value="old('name')" placeholder="Masukkan nama Anda" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input id="email" class="form-control" type="email" name="email" :value="old('email')" placeholder="contoh@email.com" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" class="form-label" />
            <div class="input-group">
                <x-text-input id="password" class="form-control" type="password" name="password" placeholder="Minimal 8 karakter" required />
                <button id="togglePassword" class="btn btn-outline-secondary" type="button" title="Tampilkan password" aria-label="Tampilkan password" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="form-label" />
            <div class="input-group">
                <x-text-input id="password_confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Ulangi password" required />
                <button id="togglePasswordConfirmation" class="btn btn-outline-secondary" type="button" title="Tampilkan password" aria-label="Tampilkan password" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mb-3 mt-4">
            <h6 class="fw-bold border-bottom pb-2">Data Usaha</h6>
        </div>

        <div class="mb-3">
            <x-input-label for="store_name" :value="__('Nama Usaha / Toko')" class="form-label" />
            <x-text-input id="store_name" class="form-control" type="text" name="store_name" :value="old('store_name')" placeholder="Misal: Warung Makmur" required />
            <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="store_phone" :value="__('No. HP Usaha (Opsional)')" class="form-label" />
            <x-text-input id="store_phone" class="form-control" type="text" name="store_phone" :value="old('store_phone')" placeholder="Misal: 0812..." />
            <x-input-error :messages="$errors->get('store_phone')" class="mt-2" />
        </div>

        <div class="d-grid mb-3">
            <x-primary-button class="btn btn-brand w-100 py-2">{{ __('Daftar Sekarang') }}</x-primary-button>
        </div>

        <p class="text-center text-muted mb-0 small">Sudah memiliki akun? <a href="{{ route('login') }}" class="text-primary text-decoration-none">Masuk</a></p>
    </form>

    <script>
        function setupToggle(buttonId, inputId) {
            const btn = document.getElementById(buttonId);
            if(btn) {
                btn.addEventListener('click', function () {
                    const passwordInput = document.getElementById(inputId);
                    const isHidden = passwordInput.type === 'password';

                    passwordInput.type = isHidden ? 'text' : 'password';
                    this.setAttribute('aria-pressed', String(isHidden));
                    this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                    this.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                    this.querySelector('i').className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }
        }
        setupToggle('togglePassword', 'password');
        setupToggle('togglePasswordConfirmation', 'password_confirmation');
    </script>
</x-guest-layout>
