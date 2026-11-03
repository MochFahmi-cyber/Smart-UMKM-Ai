<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Kasir Mandiri - Smart UMKM AI</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- UI CSS -->
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ filemtime(public_path('css/ui.css')) }}">

    <style>
        body {
            background-color: #f8fafc;
        }
        /* Override dark mode just for customer kasir to keep it bright and simple */
        .customer-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1020;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem;
        }
        .customer-brand {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.5px;
        }
        .customer-brand i {
            color: #2563eb;
        }
        .customer-main {
            padding: 1rem;
            padding-bottom: 120px; /* space for sticky cart */
            max-width: 1200px;
            margin: 0 auto;
        }
        .product-card {
            transition: all 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,.08) !important;
        }
        .product-card:active {
            transform: scale(0.98);
        }
        .product-name {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            color: #1e293b;
            margin-bottom: 0.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.6em;
        }
        .product-price {
            font-weight: 800;
            color: #2563eb;
            font-size: 1.15rem;
        }
        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 1rem;
            z-index: 1030;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
        }
        .btn-add-cart {
            border-radius: 999px;
            font-weight: 600;
        }
        .cart-qty-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }
    </style>
</head>
<body>
    <header class="customer-header d-flex justify-content-between align-items-center">
        <div class="customer-brand">
            <i class="bi bi-shop"></i> Kasir Mandiri
        </div>
        <button class="btn btn-light rounded-pill position-relative" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
            <i class="bi bi-cart3 fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge" style="display: {{ (isset($cart) && count($cart) > 0) ? 'block' : 'none' }}">
                {{ isset($cart) ? collect($cart)->sum('quantity') : 0 }}
            </span>
        </button>
    </header>

    <main class="customer-main">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    <!-- Global Loader -->
    <div id="globalLoader" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white" style="z-index: 9999; opacity: 0.7; display: none !important; transition: opacity 0.2s;">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('globalLoader');
        
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.classList && !e.target.classList.contains('no-loader')) {
                loader.style.setProperty('display', 'flex', 'important');
            }
        }, true);

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && !link.href.startsWith('javascript') && link.target !== '_blank' && !link.classList.contains('no-loader') && !link.hasAttribute('download')) {
                loader.style.setProperty('display', 'flex', 'important');
            }
        }, true);
    });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            document.getElementById('globalLoader').style.setProperty('display', 'none', 'important');
        }
    });
    </script>
</body>
</html>
