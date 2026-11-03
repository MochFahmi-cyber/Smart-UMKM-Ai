<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <title>
        {{ config('app.name', 'Smart UMKM AI') }}
    </title>


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">


    <!-- Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.4/font/bootstrap-icons.css">


    {{-- ui.css adalah stylesheet dashboard yang aktif. Parameter versi mencegah
         browser memakai CSS lama setelah layout diperbarui. --}}
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ filemtime(public_path('css/ui.css')) }}">


    @stack('styles')


</head>


<body class="dashboard-body">


<div class="app-shell d-flex min-vh-100">


    @include('layouts.navigation')



    <div class="flex-fill app-main">



        <!-- TOP NAVBAR -->

        <nav class="navbar main-navbar px-4 py-3">


            <div class="container-fluid">


                <button 
                class="btn sidebar-toggle d-lg-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu"
                aria-label="Buka menu navigasi">

                    <i class="bi bi-list"></i>

                </button>





                <div class="d-flex align-items-center gap-2 gap-sm-3">


                    <div class="brand-title">

                        <span class="d-none d-sm-inline">
                            {{ config('app.name','Smart UMKM AI') }}
                        </span>

                        <span class="d-sm-none">
                            <i class="bi bi-robot me-1"></i>
                            Smart UMKM
                        </span>

                    </div>



                    <span class="ai-badge d-none d-md-inline-flex">

                        <i class="bi bi-robot me-1"></i>

                        AI Dashboard

                    </span>


                </div>





                <!-- USER -->

                <div class="ms-auto d-flex align-items-center gap-3">


                    <button id="themeToggleBtn" class="btn btn-link border-0 text-decoration-none p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" type="button" aria-label="Ubah tema">
                        <i class="bi bi-sun-fill text-warning fs-5" id="themeIcon"></i>
                    </button>


                    <div class="d-none d-md-block welcome-text">

                        Selamat datang,
                        <strong>
                            {{ auth()->user()->name }}
                        </strong>

                    </div>





                    <div class="dropdown">


                        <a class="profile-button dropdown-toggle"
                           href="#"
                           data-bs-toggle="dropdown">


                            <div class="profile-mini">

                                <i class="bi bi-person-fill"></i>

                            </div>


                            <span class="d-none d-md-inline">

                                {{ auth()->user()->name }}

                            </span>


                        </a>





                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">


                            <li>

                                <a class="dropdown-item py-2"
                                   href="{{ route('profile.edit') }}">

                                    <i class="bi bi-person me-2"></i>

                                    Profile

                                </a>

                            </li>



                            <li>

                                <form method="POST"
                                      action="{{ route('logout') }}">

                                    @csrf


                                    <button class="dropdown-item py-2">

                                        <i class="bi bi-box-arrow-right me-2"></i>

                                        Logout

                                    </button>


                                </form>

                            </li>


                        </ul>


                    </div>


                </div>


            </div>


        </nav>







        <main class="content-wrapper">


            @isset($header)

                <div class="page-heading">

                    {{ $header }}

                </div>

            @endisset



            <div class="dashboard-content">

                {{ $slot }}

            </div>


        </main>



    </div>


</div>





<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebarMenu');
        if (sidebar && window.bootstrap) {
            // Pada layar kecil, tutup menu setelah pengguna memilih halaman.
            sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.matchMedia('(max-width: 991.98px)').matches) {
                        bootstrap.Offcanvas.getOrCreateInstance(sidebar).hide();
                    }
                });
            });
        }

        // Theme switching logic
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        
        function updateThemeUI(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-moon-stars-fill text-info fs-5';
            } else {
                themeIcon.className = 'bi bi-sun-fill text-warning fs-5';
            }
        }
        
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateThemeUI(currentTheme);
        
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeUI(newTheme);
            });
        }
        
        // Global Real-time Search & Filter via AJAX
        const searchForms = document.querySelectorAll('form[method="GET"]');
        searchForms.forEach(searchForm => {
            // Check if form has a search input or filter to attach AJAX
            const hasInputs = searchForm.querySelector('input[name="q"], select');
            if (!hasInputs) return;

            let searchTimer;
            
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
            });

            searchForm.querySelectorAll('input, select').forEach(element => {
                const eventType = element.tagName === 'SELECT' ? 'change' : 'input';
                
                element.addEventListener(eventType, function() {
                    clearTimeout(searchTimer);
                    
                    searchTimer = setTimeout(() => {
                        const url = new URL(window.location.href);
                        const formData = new FormData(searchForm);
                        
                        // Clear existing params to avoid keeping old ones if they are removed from form
                        const keysToRemove = [];
                        for (let key of url.searchParams.keys()) {
                            if (key !== 'page') keysToRemove.push(key);
                        }
                        keysToRemove.forEach(k => url.searchParams.delete(k));

                        // Set new params
                        for (let [key, value] of formData.entries()) {
                            if (value) {
                                url.searchParams.set(key, value);
                            }
                        }
                        url.searchParams.delete('page'); // Reset to first page
                        
                        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            // Replace table content
                            const currentTableContainer = document.querySelector('.table-responsive');
                            const newTableContainer = doc.querySelector('.table-responsive');
                            
                            if (currentTableContainer && newTableContainer) {
                                currentTableContainer.innerHTML = newTableContainer.innerHTML;
                            }
                            
                            // Replace Pagination
                            const currentPagination = document.querySelector('.pagination')?.closest('div, nav');
                            const newPagination = doc.querySelector('.pagination')?.closest('div, nav');
                            
                            if (currentPagination && newPagination) {
                                currentPagination.innerHTML = newPagination.innerHTML;
                            } else if (currentPagination && !newPagination) {
                                currentPagination.innerHTML = '';
                            } else if (!currentPagination && newPagination && currentTableContainer) {
                                const pagWrapper = document.createElement('div');
                                pagWrapper.className = 'mt-3';
                                pagWrapper.innerHTML = newPagination.innerHTML;
                                currentTableContainer.parentNode.insertBefore(pagWrapper, currentTableContainer.nextSibling);
                            }
                            
                            // Update URL silently
                            window.history.replaceState({}, '', url);
                        })
                        .catch(error => console.error('Search error:', error));
                    }, 300); // 300ms debounce
                });
            });
        });
    });
</script>


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
