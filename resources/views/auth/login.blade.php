<x-guest-layout class="auth-page auth-page-login">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-login-form">
        @csrf

        <div class="mb-4 text-center d-none d-lg-block">
            <h2 class="h4 fw-bold mb-2">Masuk ke Akun Anda</h2>
            <p class="text-muted mb-0">Gunakan email dan password Anda untuk masuk ke dashboard bisnis.</p>
        </div>

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input id="email" class="form-control form-control-lg" type="email" name="email" :value="old('email')" placeholder="contoh@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" class="form-label" />
            <div class="input-group input-group-lg">
                <x-text-input id="password" class="form-control" type="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="current-password" />
                <button id="togglePassword" class="btn btn-outline-secondary" type="button" title="Tampilkan password" aria-label="Tampilkan password" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-4">
            <div class="form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
            </div>
            @if (Route::has('password.request'))
                <a class="text-sm text-primary" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <div class="d-grid mb-3">
            <x-primary-button class="btn btn-brand btn-lg w-100">{{ __('Log in') }}</x-primary-button>
        </div>

        <p class="text-center text-muted mb-0">Belum punya akun? <a href="{{ route('register') }}" class="text-primary text-decoration-none">Daftar sekarang</a></p>
    </form>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(isHidden));
            this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            this.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            this.querySelector('i').className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</x-guest-layout>
