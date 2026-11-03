<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-1">Profil Saya</h2>
                <p class="text-muted mb-0">Kelola pengaturan dan informasi profil Anda.</p>
            </div>
            <span class="badge bg-primary px-3 py-2 rounded-pill">
                {{ ucfirst($user->role ?? 'User') }}
            </span>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    @include('profile.partials.update-payment-settings-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-danger border-opacity-50">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
