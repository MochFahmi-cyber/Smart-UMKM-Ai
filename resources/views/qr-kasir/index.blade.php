<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-1">QR Kasir Pelanggan</h2>
        <p class="text-muted mb-0">Kelola akses kasir mandiri untuk pelanggan melalui QR Code.</p>
    </x-slot>

<div class="container-fluid">
    <div class="d-flex justify-content-end mb-4">
        <form action="{{ route('qr-kasir.generate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Generate Token Baru
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Token</th>
                            <th>Status</th>
                            <th>URL Kasir</th>
                            <th>Dibuat Pada</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tokens as $token)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace">{{ $token->token }}</td>
                                <td>
                                    @if($token->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Aktif</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control" value="{{ route('customer-kasir.index', $token->token) }}" readonly id="url_{{ $token->token }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('url_{{ $token->token }}')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $token->created_at->format('d M Y, H:i') }}</td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('qr-kasir.toggle', $token->token) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($token->is_active)
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Nonaktifkan">
                                                <i class="bi bi-power"></i> Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                                <i class="bi bi-check-circle"></i> Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                    <button class="btn btn-sm btn-primary ms-1" onclick="showQR('{{ route('customer-kasir.index', $token->token) }}', '{{ $token->token }}')" data-bs-toggle="modal" data-bs-target="#qrModal">
                                        <i class="bi bi-qr-code"></i> Tampilkan QR
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="bi bi-qr-code-scan fs-1 text-secondary"></i>
                                    </div>
                                    <p class="mb-0">Belum ada QR Kasir yang dibuat.</p>
                                    <p class="small">Klik tombol <strong>Generate Token Baru</strong> untuk mulai.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="qrModalLabel">QR Code Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pb-4 pt-4">
                <div id="qrcode-container" class="bg-white p-3 d-inline-block rounded-3 border mb-3"></div>
                <p class="text-muted small mb-0 font-monospace" id="qr-token-text"></p>
                <p class="text-muted small mb-0 mt-2">Pelanggan dapat memindai QR ini untuk berbelanja mandiri.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Load QRCode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        // Change icon temporarily
        var btn = copyText.nextElementSibling;
        var icon = btn.querySelector('i');
        icon.className = 'bi bi-check2 text-success';
        setTimeout(() => {
            icon.className = 'bi bi-clipboard';
        }, 2000);
    }

    let qrcode = null;
    function showQR(url, token) {
        document.getElementById('qr-token-text').innerText = 'Token: ' + token;
        var container = document.getElementById("qrcode-container");
        container.innerHTML = "";
        qrcode = new QRCode(container, {
            text: url,
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
</script>
@endpush
</x-app-layout>
