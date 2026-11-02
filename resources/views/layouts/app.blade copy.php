<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VeloAssets')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Gaya dasar optimasi layout agar pas dengan sketsa */
        body { overflow-x: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-premium { border-radius: 12px; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); }
        /* Efek Hover Pada Link Menu */
        .nav-link-premium:hover {
            background-color: rgba(255, 255, 255, 0.04);
            color: #ffffff !important;
        }
        
        /* Tombol Menu yang Aktif (Glow Ungu Gradasi Sesuai Foto) */
        .active-glow {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        /* Hover efek pada tulisan Header Grup */
        .group-header-link:hover {
            color: #94a3b8 !important;
        }

        /* Animasi Rotasi Otomatis Panah Indikator Group */
        .group-header-link[aria-expanded="true"] .arrow-icon {
            transform: rotate(90deg);
            color: #6366f1;
        }
    </style>
</head>
<body>

    <div class="d-flex" style="min-height: 100vh; background-color: #f8fafc;">
        
        <div class="bg-dark text-white flex-shrink-0 d-flex flex-column" style="width: 260px; background: #0f172a !important; border-right: 1px solid #1e293b;">
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
                                    
                                </div>
                            </div>

                        </div>
                    @endforeach

                </div>
            </div>
        </div>


        <div class="flex-grow-1 d-flex flex-column min-vh-100">
            
            <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center m-0 rounded-0" style="z-index: 1020;">
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

                    <button class="btn btn-light border-0 shadow-sm rounded-circle me-3 d-flex justify-content-center align-items-center" id="themeToggleBtn" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-moon text-secondary" id="themeIcon"></i>
                    </button>

                    <div class="position-relative text-secondary px-2" style="cursor: pointer;">
                        <i class="fa-regular fa-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </div>
                    
                    <div class="d-flex align-items-center border-start ps-4">
                        <div class="me-3 text-end d-none d-sm-block">
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.85rem;"><span class="fw-semibold">Hi, {{ Auth::user()->name ?? 'Administrator' }}</span></h6>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Asset Controller</small>
                        </div>
                        
                        <form action="{{ route('logout') }}" method="POST" class="m-0 d-flex align-items-center gap-3">
                            @csrf
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 0.85rem; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                                AI
                            </div>
                            <button type="submit" class="btn btn-link p-0 text-muted" title="Keluar Sistem" style="box-shadow: none; border: none;">
                                <i class="fa-solid fa-right-from-bracket fs-5" style="color: #64748b;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <!-- <div class="bg-white border-bottom px-4 py-3 m-0 rounded-0">
                @yield('header') 
            </div> -->


            <div class="p-4 flex-grow-1">
                @yield('content')
            </div>


            <div class="bg-white border-top px-4 py-3 text-center text-muted mt-auto" style="font-size: 0.82rem; background: #ffffff;">
                &copy; {{ date('Y') }} <span class="fw-bold text-dark">VeloAssets Management Suite</span>. Sistem Terintegrasi Operasional Unit Korporat.
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            const htmlTag = document.documentElement; // Mengambil tag <html> utama

            // 1. Cek apakah sebelumnya user sudah memilih dark mode
            const savedTheme = localStorage.getItem('velo_theme') || 'light';
            applyTheme(savedTheme);

            // 2. Event saat tombol diklik
            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlTag.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                applyTheme(newTheme);
            });

            // 3. Fungsi untuk menerapkan warna dan ikon
            function applyTheme(theme) {
                // Set atribut di HTML
                htmlTag.setAttribute('data-theme', theme);
                // Simpan ke memori browser
                localStorage.setItem('velo_theme', theme);

                if (theme === 'dark') {
                    // Ubah ikon ke Matahari
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                    themeIcon.classList.replace('text-secondary', 'text-warning');
                    themeToggleBtn.classList.replace('btn-light', 'btn-dark');
                } else {
                    // Ubah ikon ke Bulan
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                    themeIcon.classList.replace('text-warning', 'text-secondary');
                    themeToggleBtn.classList.replace('btn-dark', 'btn-light');
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>