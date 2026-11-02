<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VeloAssets')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            --app-bg: #f5f7fb;
            --app-text: #172033;
            --app-muted: #718096;
            --app-border: #e7ebf2;
            --app-accent: #4f46e5;
            --app-sidebar: #111827;
        }

        body {
            overflow-x: hidden;
            background: var(--app-bg);
            color: var(--app-text);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 0.92rem;
            -webkit-font-smoothing: antialiased;
        }

        .app-shell { min-height: 100vh; background: var(--app-bg); }
        .app-sidebar {
            width: 260px;
            background: var(--app-sidebar) !important;
            border-right: 1px solid rgba(255,255,255,.06);
        }
        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            min-height: 72px;
            border-color: var(--app-border) !important;
            box-shadow: 0 2px 12px rgba(20, 32, 54, .035);
        }
        .app-page-content { padding: 28px 32px !important; }
        .app-page-heading { margin: 0 0 22px; }
        .app-page-heading h1,
        .app-page-heading h2,
        .app-page-heading h3 { letter-spacing: -.035em; }
        .card-premium {
            border: 1px solid rgba(226, 232, 240, .8);
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(20, 32, 54, .045);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .card-premium:hover { box-shadow: 0 9px 26px rgba(20, 32, 54, .075); }
        .card-premium > .card-header { border-color: var(--app-border) !important; }
        .form-control,
        .form-select {
            min-height: 42px;
            border-color: #dfe5ee;
            border-radius: 10px;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 .22rem rgba(79, 70, 229, .12);
        }
        .btn { border-radius: 9px; font-weight: 600; transition: transform .18s ease, box-shadow .18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .table-responsive { border-radius: inherit; }
        .table-premium thead th {
            padding: 14px 18px;
            border-bottom: 1px solid var(--app-border);
            background: #f8f9fc;
            color: #64748b;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .055em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .table-premium tbody td {
            padding: 14px 18px;
            border-color: #eef1f6;
            color: #39465b;
            vertical-align: middle;
        }
        .table > :not(caption) > * > * { box-shadow: none; }
        .table tbody tr { transition: background-color .15s ease; }
        .table tbody tr:hover { background-color: #fafbff; }
        .app-menu-toggle { display: none; }
        .app-sidebar-backdrop { display: none; }

        .nav-link-premium:hover {
            background-color: rgba(255, 255, 255, .075);
            color: #ffffff !important;
        }
        .active-glow {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 7px 18px rgba(79, 70, 229, .28);
        }
        .group-header-link:hover { color: #cbd5e1 !important; }
        .group-header-link[aria-expanded="true"] .arrow-icon {
            transform: rotate(90deg);
            color: #6366f1;
        }

        @media (max-width: 767.98px) {
            .app-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 1045;
                width: 280px !important;
                height: 100vh;
                overflow-y: auto;
                transform: translateX(-100%);
                transition: transform .22s ease;
            }
            body.sidebar-open .app-sidebar { transform: translateX(0); }
            .app-sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1040;
                display: block;
                background: rgba(15, 23, 42, .48);
                opacity: 0;
                pointer-events: none;
                transition: opacity .22s ease;
            }
            body.sidebar-open .app-sidebar-backdrop { opacity: 1; pointer-events: auto; }
            .app-menu-toggle { display: inline-flex; }
            .app-topbar { min-height: 64px; padding: 12px 16px !important; }
            .app-page-content { padding: 22px 16px !important; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>

    <div class="app-shell d-flex">
        
        <aside class="app-sidebar bg-dark text-white flex-shrink-0 d-flex flex-column">
            <div class="p-4 w-100">
                
                <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-20">
                    <div class="bg-primary bg-gradient rounded-3 p-2 me-2 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                        <i class="fas fa-box-open small"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-white m-0 tracking-wide" style="font-size: 1.05rem;">VeloAssets</h5>
                        <small class="text-muted" style="font-size: 0.68rem; letter-spacing: 0.5px;">MANAGEMENT SUITE</small>
                    </div>
                </div>

                <div class="nav-menu-container d-flex flex-column gap-3" id="accordionSidebar">
                    
                    @php
                        // TARIK DATA DARI TABEL INDUK (menu_groups) BESERTA RELASI ANAKNYA (menus)
                        // Catatan: Jika di tabelmu tidak ada kolom 'order', ganti 'order' menjadi 'id'
                        $menuGroups = \App\Models\MenuGroup::with('menus')->orderBy('id', 'asc')->get();
                    @endphp
                    
                    @foreach($menuGroups as $group)
                        @php
                            // 1. Cek apakah ada salah satu menu anak di dalam grup ini yang sedang aktif dibuka
                            $isGroupActive = false;
                            foreach($group->menus as $item) {
                                if(request()->routeIs($item->route)) { 
                                    $isGroupActive = true; 
                                    break;
                                }
                            }
                            // 2. Buat ID unik untuk HTML collapse berdasarkan ID Group database
                            $groupId = 'group-' . $group->id;
                        @endphp

                        <div class="menu-group-item">
                            
                            <a class="group-header-link d-flex justify-content-between align-items-center py-2 px-1 text-decoration-none text-uppercase tracking-wider fw-bold mb-1" 
                               data-bs-toggle="collapse" 
                               href="#collapse-{{ $groupId }}" 
                               role="button" 
                               aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}"
                               style="font-size: 0.72rem; color: #64748b; transition: color 0.2s; cursor: pointer;">
                                <span>
                                    {{ $group->name }}
                                </span>
                                <i class="fa-solid fa-chevron-right arrow-icon" style="font-size: 0.65rem; transition: transform 0.2s;"></i>
                            </a>

                            <div class="collapse {{ $isGroupActive ? 'show' : '' }}" id="collapse-{{ $groupId }}" data-bs-parent="#accordionSidebar">
                                <div class="d-flex flex-column gap-1 pt-1">
                                    
                                    @foreach($group->menus as $item)
                                        <a href="{{ $item->route && Route::has($item->route) ? route($item->route) : '#' }}" 
                                           class="nav-link-premium d-flex align-items-center py-2 px-3 rounded-3 text-decoration-none text-white-50 {{ request()->routeIs($item->route) ? 'active-glow text-white' : '' }}"
                                           style="font-size: 0.85rem; transition: all 0.2s;">
                                            
                                            @if($item->icon)
                                                <i class="{{ $item->icon }} me-2 opacity-80" style="width: 18px; font-size: 0.9rem;"></i>
                                            @else
                                                <i class="fa-regular fa-circle me-2 opacity-40" style="width: 18px; font-size: 0.45rem; text-align: center;"></i>
                                            @endif
                                            
                                            <span>{{ $item->name }}</span>
                                        </a>
                                    @endforeach

                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('users.index') }}"
                                           class="nav-link-premium d-flex align-items-center py-2 px-3 rounded-3 text-decoration-none text-white-50 {{ request()->routeIs('users.*') ? 'active-glow text-white' : '' }}"
                                           style="font-size: 0.85rem; transition: all 0.2s;">
                                            <i class="fa-solid fa-users-gear me-2" style="width: 18px; font-size: 0.9rem;"></i>
                                            <span>Pengelolaan User</span>
                                        </a>
                                    @endif

                                </div>
                            </div>

                        </div>
                    @endforeach

                    @if(Auth::user()->isAdmin())
                        <div class="menu-group-item">
                            <span class="d-block py-2 px-1 text-uppercase fw-bold mb-1" style="font-size: .72rem; color: #64748b;">Administrasi</span>
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ route('backups.index') }}" class="nav-link-premium d-flex align-items-center py-2 px-3 rounded-3 text-decoration-none text-white-50 {{ request()->routeIs('backups.*') ? 'active-glow text-white' : '' }}" style="font-size: .85rem;">
                                    <i class="fa-solid fa-database me-2" style="width: 18px; font-size: .9rem;"></i>
                                    <span>Backup Data</span>
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </aside>
        <button type="button" class="app-sidebar-backdrop border-0 p-0" aria-label="Tutup menu"></button>


        <div class="flex-grow-1 d-flex flex-column min-vh-100">
            
            <div class="app-topbar bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center m-0 rounded-0">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="app-menu-toggle btn btn-light border align-items-center justify-content-center" id="appMenuToggle" aria-label="Buka menu navigasi" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="d-none d-md-block">
                        <span class="d-block text-muted text-uppercase fw-bold" style="font-size: .67rem; letter-spacing: .1em;">VeloAssets Workspace</span>
                        <span class="d-block fw-bold text-dark" style="font-size: .92rem;">@yield('title', 'Dashboard')</span>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-4">
                    <div class="bg-light px-3 py-2 rounded-3 text-secondary small fw-semibold d-none d-md-block" style="font-size: 0.8rem;">
                        <i class="fa-regular fa-calendar text-primary me-2"></i>{{ date('l, d F Y') }}
                    </div>

                    <div class="position-relative text-secondary px-2" style="cursor: pointer;">
                        <i class="fa-regular fa-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </div>
                    
                    <div class="d-flex align-items-center border-start ps-4">
                        <div class="me-3 text-end d-none d-sm-block">
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.85rem;"><span class="fw-semibold">Hi, {{ Auth::user()->name ?? 'Administrator' }}</span></h6>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ Auth::user()->isAdmin() ? 'Admin' : (Auth::user()->role ?? 'User') }}</small>
                        </div>
                        
                        <form action="{{ route('logout') }}" method="POST" class="m-0 d-flex align-items-center gap-3">
                            @csrf
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 0.85rem; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                                {{ collect(explode(' ', Auth::user()->name ?? 'A'))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                            </div>
                            <button type="submit" class="btn btn-link p-0 text-muted" title="Keluar Sistem" style="box-shadow: none; border: none;">
                                <i class="fa-solid fa-right-from-bracket fs-5" style="color: #64748b;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <main class="app-page-content flex-grow-1">
                @hasSection('header')
                    <section class="app-page-heading">
                        @yield('header')
                    </section>
                @endif
                @yield('content')
            </main>


            <div class="bg-white border-top px-4 py-3 text-center text-muted mt-auto" style="font-size: 0.82rem; background: #ffffff;">
                &copy; {{ date('Y') }} <span class="fw-bold text-dark">VeloAssets Management Suite</span>. Sistem Terintegrasi Operasional Unit Korporat.
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.getElementById('appMenuToggle');
            const backdrop = document.querySelector('.app-sidebar-backdrop');

            const closeMenu = () => {
                document.body.classList.remove('sidebar-open');
                menuToggle?.setAttribute('aria-expanded', 'false');
            };

            menuToggle?.addEventListener('click', () => {
                const isOpen = document.body.classList.toggle('sidebar-open');
                menuToggle.setAttribute('aria-expanded', String(isOpen));
            });
            backdrop?.addEventListener('click', closeMenu);
            document.querySelectorAll('.app-sidebar .nav-link-premium').forEach((link) => {
                link.addEventListener('click', closeMenu);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMenu();
            });
        });
    </script>
    @yield('scripts')
</body>
</html>