<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h2 class="h4 mb-1">AI Asisten</h2>
                <p class="text-muted mb-0">Asisten AI untuk menganalisis pelanggan, transaksi, produk, stok, dan arus kas usaha.</p>
            </div>
            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2">
                <i class="bi bi-robot me-2"></i>
                Smart UMKM AI
            </span>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <div class="p-4 border-bottom border-light">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-robot fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Smart UMKM AI, teman untuk mengelola usaha</h5>
                                    <p class="mb-0 text-muted">Pantau penjualan dan stok, lalu dapatkan saran yang mudah diterapkan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                                <form action="{{ route('ai-assistant.clear') }}" method="POST" onsubmit="return confirm('Hapus seluruh riwayat chat?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus riwayat chat" aria-label="Hapus riwayat chat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Berapa jumlah transaksi dan omzet saya hari ini?">Transaksi hari ini</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Produk apa yang paling laris dalam 30 hari terakhir?">Produk terlaris</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Siapa saja pelanggan yang membeli dalam 30 hari terakhir dan apa yang mereka beli?">Pelanggan pembeli</button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Produk apa yang stoknya habis atau menipis?">Stok perlu diisi</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Produk mana yang perlu saya restok lebih dulu berdasarkan stok dan penjualan?">Prioritas restok</button>
                                <button type="button" class="btn btn-outline-primary btn-sm quick-question" data-message="Analisis penjualan saya berdasarkan data transaksi 30 hari terakhir.">Analisis transaksi</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-shell p-3 p-md-4" id="chatShell" style="overflow-y: auto;">
                    @php($messages = $messages ?? [])
                    @if (empty($messages))
                        <div class="d-flex justify-content-start mb-3">
                            <div class="chat-bubble chat-bubble--ai">
                                <div class="fw-semibold mb-1">Smart UMKM AI</div>
                                <div>Halo! Saya siap mengubah data penjualan dan stok Anda menjadi insight serta rekomendasi yang praktis.</div>
                                <div class="chat-time">{{ now()->format('H:i') }}</div>
                            </div>
                        </div>
                    @else
                        @foreach ($messages as $message)
                            <div class="d-flex {{ $message['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                                <div class="chat-bubble {{ $message['role'] === 'user' ? 'chat-bubble--user' : 'chat-bubble--ai' }}">
                                    <div class="fw-semibold mb-1">{{ $message['role'] === 'user' ? 'Anda' : 'Smart UMKM AI' }}</div>
                                    <div class="ai-message">{!! $message['content'] !!}</div>
                                    <div class="chat-time">{{ $message['time'] ?? now()->format('H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div id="typingIndicator" class="d-none mb-3">
                        <div class="d-flex justify-content-start">
                            <div class="chat-bubble chat-bubble--ai shadow-sm">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                    <span class="ms-2">AI sedang mengetik...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top bg-transparent p-3 p-md-4">
                    <form id="chatForm" class="d-flex gap-2 align-items-end">
                        @csrf
                        <div class="flex-grow-1">
                            <textarea id="messageInput" class="form-control" rows="2" placeholder="Tanyakan tentang pelanggan, penjualan, stok, atau kas..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .chat-shell::-webkit-scrollbar {
                width: 8px;
            }

            .chat-shell::-webkit-scrollbar-thumb {
                background: rgba(37, 99, 235, 0.25);
                border-radius: 999px;
            }

            .chat-bubble {
                max-width: 75%;
                padding: 0.9rem 1rem;
                border-radius: 1rem;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
                border: 1px solid rgba(226, 232, 240, 0.9);
                line-height: 1.5;
            }

            .chat-bubble--user {
                background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
                color: #fff;
                border-bottom-right-radius: 0.35rem;
            }

            .chat-bubble--ai {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                border-bottom-left-radius: 0.35rem;
            }

            .chat-time {
                font-size: 0.75rem;
                opacity: 0.7;
                margin-top: 0.35rem;
            }

            .typing-dot {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 999px;
                background: #93c5fd;
                animation: blink 1.2s infinite ease-in-out;
            }

            .typing-dot:nth-child(2) {
                animation-delay: 0.2s;
            }

            .typing-dot:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes blink {
                0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
                40% { transform: scale(1); opacity: 1; }
            }

@media (max-width: 768px) {
                .chat-bubble {
                    max-width: 90%;
                }
            }

            @media (max-width: 575.98px) {
                .chat-shell {
                    height: 52vh;
                    min-height: 320px;
                }
                .chat-bubble {
                    max-width: 95%;
                    padding: 0.75rem 0.85rem;
                    font-size: 0.9rem;
                }
                .chat-bubble .chat-time {
                    font-size: 0.68rem;
                }
                .quick-question {
                    font-size: 0.78rem;
                    padding: 0.35rem 0.6rem;
                }
                #messageInput {
                    font-size: 0.9rem;
                }
            }

            @media (min-width: 576px) {
                .chat-shell { height: 70vh; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chatShell = document.getElementById('chatShell');
                const chatForm = document.getElementById('chatForm');
                const messageInput = document.getElementById('messageInput');
                const typingIndicator = document.getElementById('typingIndicator');

                function scrollToBottom() {
                    chatShell.scrollTop = chatShell.scrollHeight;
                }

                function getWibTime() {
                    return new Intl.DateTimeFormat('id-ID', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        hourCycle: 'h23'
                    }).format(new Date());
                }

                function appendMessage(role, content, time) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'd-flex ' + (role === 'user' ? 'justify-content-end' : 'justify-content-start') + ' mb-3';

                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble ' + (role === 'user' ? 'chat-bubble--user' : 'chat-bubble--ai');

                    const author = document.createElement('div');
                    author.className = 'fw-semibold mb-1';
                    author.textContent = role === 'user' ? 'Anda' : 'Smart UMKM AI';

                   const body = document.createElement('div');
body.className = 'ai-message';
body.innerHTML = content;

                    const timeEl = document.createElement('div');
                    timeEl.className = 'chat-time';
                    timeEl.textContent = time;

                    bubble.appendChild(author);
                    bubble.appendChild(body);
                    bubble.appendChild(timeEl);
                    wrapper.appendChild(bubble);
                    chatShell.insertBefore(wrapper, typingIndicator);
                    scrollToBottom();
                }

                chatForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const message = messageInput.value.trim();
                    if (!message) {
                        return;
                    }

                    const time = getWibTime();
                    appendMessage('user', message, time);
                    messageInput.value = '';
                    typingIndicator.classList.remove('d-none');
                    scrollToBottom();

                    fetch('{{ route('ai-assistant.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: message })
                    })
                    .then(response => response.json())
                    .then(data => {
                        typingIndicator.classList.add('d-none');
                        appendMessage('assistant', data.reply, data.messages[data.messages.length - 1].time);
                    })
                    .catch(() => {
                        typingIndicator.classList.add('d-none');
                        appendMessage('assistant', 'Maaf, terjadi kesalahan saat menghubungkan AI.', getWibTime());
                    });
                });

                messageInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        chatForm.requestSubmit();
                    }
                });

                document.querySelectorAll('.quick-question').forEach(function (button) {
                    button.addEventListener('click', function () {
                        messageInput.value = this.dataset.message;
                        chatForm.requestSubmit();
                    });
                });

                scrollToBottom();
            });
        </script>
    @endpush
</x-app-layout>
