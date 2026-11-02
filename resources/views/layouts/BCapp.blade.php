<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enterprise Asset Management')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { 
            --font-main: 'Plus Jakarta Sans', sans-serif; 
            --bg-global: #f8fafc; 
            --sidebar-bg: #0f172a; 
            --sidebar-hover: #1e293b; 
            --accent-premium: #6366f1; 
        }
        body { 
            font-family: var(--font-main); 
            background-color: var(--bg-global); 
            color: #334155; 
            overflow-x: hidden; 
        }
        
        /* SIDEBAR STYLE */
        .sidebar { 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 270px; 
            background-color: var(--sidebar-bg); 
            z-index: 100; 
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.05); 
        }
        .sidebar .brand-area { padding: 30px 24px; }
        .sidebar .nav-link { 
            color: #94a3b8; 
            padding: 14px 20px; 
            font-weight: 500; 
            font-size: 0.95rem; 
            display: flex; 
            align-items: center; 
            border-radius: 12px; 
            margin: 4px 20px; 
            transition: all 0.25s ease; 
            text-decoration: none; 
        }
        .sidebar .nav-link:hover { background-color: var(--sidebar-hover); color: #f1f5f9; }
        .sidebar .nav-link.active { 
            background-color: var(--accent-premium); 
            color: #ffffff; 
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); 
        }
        .sidebar .nav-link i { width: 28px; font-size: 1.1rem; }
        
        /* ACCORDION STYLE */
        .sidebar-group-header { transition: background-color 0.2s ease; }
        .sidebar-group-header:hover .group-name-text { color: #f1f5f9 !important; }
        .sidebar-group-header .chevron-icon { transition: transform 0.2s ease; }
        .sidebar-group-header[aria-expanded="true"] .chevron-icon { 
            transform: rotate(90deg); 
            color: var(--accent-premium) !important; 
        }
        
        /* CONTENT AREA */
        .main-content { margin-left: 270px; padding: 30px 45px; min-height: 100vh; }
        .card-premium { 
            background: #ffffff; 
            border: none; 
            border-radius: 0px; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02); 
            transition: transform 0.3s ease; 
        }
        .icon-shape { 
            width: 52px; 
            height: 52px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 14px; 
        }
        .badge-soft-success { background-color: #dcfce7; color: #15803d; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
        .badge-soft-info { background-color: #e0f2fe; color: #0369a1; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
        .badge-soft-warning { background-color: #fef9c3; color: #a16207; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
        .badge-soft-danger { background-color: #fee2e2; color: #b91c1c; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
        .badge-soft-secondary { background-color: #f1f5f9; color: #475569; font-weight: 600; padding: 6px 12px; border-radius: 8px; }
        
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-area">
            <h4 class="text-white fw-bold mb-0 d-flex align-items-center">
                <i class="fa-solid fa-layer-group text-primary me-3"></i>VeloAssets
            </h4>
            <span style="font-size: 0.75rem; color: #64748b; letter-spacing: 1px;" class="text-uppercase fw-bold d-block mt-1">Management Suite</span>
        </div>
        <div class="px-3"><hr class="text-secondary opacity-25 my-2"></div>
        
        <div class="sidebar-menu-wrapper mt-3" id="sidebarAccordion">
            @foreach($menuGroups as $group)
                @if($group->menus->count() > 0)
                    @php
                        $isGroupActive = false;
                        foreach($group->menus as $menu) { 
                            if(Route::currentRouteName() == $menu->route) { 
                                $isGroupActive = true; 
                                break; 
                            } 
                        }
                    @endphp
                    
                    <div class="px-4 py-2 my-1 d-flex justify-content-between align-items-center sidebar-group-header" 
                         data-bs-toggle="collapse" 
                         data-bs-target="#collapse-group-{{ $group->id }}" 
                         aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}" 
                         style="cursor: pointer;">
                        <span style="font-size: 0.68rem; color: #475569; letter-spacing: 0.8px;" class="text-uppercase fw-bold group-name-text">
                            {{ $group->name }}
                        </span>
                        <i class="fa-solid fa-chevron-right text-muted small chevron-icon"></i>
                    </div>
                    
                    <div class="collapse {{ $isGroupActive ? 'show' : '' }}" 
                         id="collapse-group-{{ $group->id }}" 
                         data-bs-parent="#sidebarAccordion">
                        <ul class="nav flex-column mb-2">
                            @foreach($group->menus as $menu)
                                <li class="nav-item">
                                    <a class="nav-link {{ Route::currentRouteName() == $menu->route ? 'active' : '' }}" href="{{ route($menu->route) }}">
                                        <i class="{{ $menu->icon }}"></i> {{ $menu->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    
    <div class="main-content">
        <div class="card-premium p-3 mb-5 d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center" style="width: 35%;">
                <div class="input-group" style="border-radius: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; overflow: hidden;">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3"><i class="fa-solid fa-magnifying-glass small"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 small py-2" placeholder="Cari sesuatu..." style="font-size: 0.85rem; box-shadow: none;">
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
                        <h6 class="fw-bold text-dark m-0" style="font-size: 0.85rem;">Admin Ica</h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Asset Controller</small>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST" class="m-0 d-flex align-items-center gap-3">
                        @csrf
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 0.85rem; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                            AI
                        </div>
                        <button type="submit" class="btn btn-link p-0 text-muted" title="Keluar Sistem" style="box-shadow: none; border: none;">
                            <i class="fa-solid fa-right-from-bracket fs-5" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'" style="color: #64748b; transition: color 0.2s;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    @if(session('error'))
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-4" style="z-index: 1060; margin-top: 15px;">
            <div id="balloonToast" class="toast align-items-center bg-white border-0 rounded-4 shadow-lg" role="alert" data-bs-delay="4000">
                <div class="d-flex p-2" style="min-width: 320px;">
                    <div class="toast-body d-flex align-items-center w-100">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;"><i class="fa-solid fa-triangle-exclamation fs-5"></i></div>
                        <div><h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Notifikasi</h6><span class="text-muted small">{{ session('error') }}</span></div>
                    </div>
                    <button type="button" class="btn-close m-auto me-2" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toastEl = document.getElementById('balloonToast');
                if(toastEl) {
                    new bootstrap.Toast(toastEl).show();
                }
            });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>