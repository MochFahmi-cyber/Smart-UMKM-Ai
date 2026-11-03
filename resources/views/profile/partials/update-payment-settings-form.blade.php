<section>
    <header>
        <h3 class="h5 text-dark fw-bold mb-1">
            <i class="bi bi-wallet2 text-primary me-2"></i>Pengaturan Pembayaran
        </h3>
        <p class="text-muted small">
            Atur metode pembayaran (Transfer Bank & QRIS) yang akan ditampilkan kepada pelanggan saat mereka melakukan checkout mandiri.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update-payment') }}" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('patch')

        <div class="row g-3">
            <div class="col-12">
                <h6 class="fw-bold mb-2">Transfer Bank</h6>
            </div>
            
            <div class="col-md-4">
                <label for="bank_name" class="form-label fw-semibold">Nama Bank</label>
                <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name', $user->store->bank_name ?? '') }}" placeholder="Contoh: BCA, Mandiri, BRI">
                <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
            </div>

            <div class="col-md-4">
                <label for="bank_account" class="form-label fw-semibold">Nomor Rekening</label>
                <input type="text" id="bank_account" name="bank_account" class="form-control" value="{{ old('bank_account', $user->store->bank_account ?? '') }}" placeholder="Contoh: 1234567890">
                <x-input-error class="mt-2" :messages="$errors->get('bank_account')" />
            </div>

            <div class="col-md-4">
                <label for="bank_account_name" class="form-label fw-semibold">Atas Nama</label>
                <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $user->store->bank_account_name ?? '') }}" placeholder="Nama pemilik rekening">
                <x-input-error class="mt-2" :messages="$errors->get('bank_account_name')" />
            </div>

            <hr>

            <div class="col-12">
                <h6 class="fw-bold mb-2">Pembayaran QRIS</h6>
            </div>

            <div class="col-md-12 mb-3">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="qris_active" name="qris_active" value="1" {{ old('qris_active', $user->store->qris_active ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="qris_active">Aktifkan Pembayaran via QRIS</label>
                </div>

                <label for="qris_image" class="form-label fw-semibold">Gambar QRIS (Opsional)</label>
                
                @if($user->store && $user->store->qris_image)
                    <div class="mb-3">
                        <label class="form-label d-block text-muted small">QRIS Saat Ini</label>
                        <img src="{{ asset('storage/' . $user->store->qris_image) }}" alt="QRIS" class="img-thumbnail" style="max-width: 150px">
                    </div>
                @endif
                
                <input type="file" id="qris_image" name="qris_image" class="form-control" accept="image/*">
                <div class="form-text">Upload gambar QRIS terbaru jika ingin mengganti.</div>
                <x-input-error class="mt-2" :messages="$errors->get('qris_image')" />
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button class="btn btn-primary px-4">Simpan Pengaturan</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-semibold" id="payment-status-message">
                    <i class="bi bi-check-circle me-1"></i>Tersimpan.
                </span>
                <script>
                    setTimeout(() => { document.getElementById('payment-status-message').style.display = 'none'; }, 3000);
                </script>
            @endif
        </div>
    </form>
</section>
