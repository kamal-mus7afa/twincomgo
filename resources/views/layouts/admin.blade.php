<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Web Application Manifest & PWA Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Twincomgo">
    <link rel="icon" sizes="512x512" href="/images/icons/icon-512x512.png">
    
    <!-- iOS Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Twincomgo">
    <link rel="apple-touch-icon" href="/images/icons/icon-512x512.png">
    
    <!-- Title & Favicon -->
    <title>Twincomgo - @yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">

    <!-- Fonts: Plus Jakarta Sans for Modern SaaS Look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Libraries CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Custom Modern CSS -->
    <style>
        :root {
            /* Premium SaaS Color Palette */
            --primary: #4f46e5;         /* Indigo 600 */
            --primary-hover: #4338ca;   /* Indigo 700 */
            --primary-light: #e0e7ff;   /* Indigo 100 */
            
            --sidebar-bg: #0f172a;      /* Slate 900 */
            --sidebar-border: #1e293b;  /* Slate 800 */
            --sidebar-item: #94a3b8;    /* Slate 400 */
            --sidebar-item-hover: #f8fafc;
            --sidebar-active-bg: #1e293b;
            --sidebar-active-text: #ffffff;
            
            --bg-app: #f8fafc;          /* Slate 50 */
            --bg-surface: #ffffff;
            
            --text-main: #0f172a;       /* Slate 900 */
            --text-muted: #64748b;      /* Slate 500 */
            --border-color: #e2e8f0;    /* Slate 200 */

            /* Dimensions */
            --sidebar-w: 280px;
            --sidebar-w-collapsed: 88px;
            --header-h: 72px;
            --mob-bottom-nav-h: 70px;
            --safe-bottom: env(safe-area-inset-bottom, 16px);
            --transition-speed: 0.3s;
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-app);
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
        }

        /* ================= SCROLLBAR ================= */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu:hover::-webkit-scrollbar-thumb { background: #334155; }

        /* ================= LOADER ================= */
        #app-loader {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999; display: none; flex-direction: column;
            justify-content: center; align-items: center;
        }
        .spinner-ring {
            width: 44px; height: 44px;
            border: 3px solid var(--primary-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .loader-text {
            margin-top: 16px; font-weight: 600; color: var(--primary); 
            font-size: 0.9rem; letter-spacing: 0.5px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ================= SIDEBAR ================= */
        .app-sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 1040; display: flex; flex-direction: column;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            height: var(--header-h); display: flex; align-items: center; 
            padding: 0 24px; text-decoration: none; color: white; gap: 14px;
        }
        .brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: bold; flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .brand-text { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px; white-space: nowrap; }

        .sidebar-menu { flex: 1; overflow-y: auto; padding: 24px 16px; }

        .menu-label {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px;
            color: #475569; font-weight: 700; margin: 24px 0 10px 12px; white-space: nowrap;
        }
        .sidebar-menu > .menu-label:first-child { margin-top: 0; }

        .menu-item { margin-bottom: 6px; }
        .menu-link {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 16px; border-radius: 10px;
            color: var(--sidebar-item); text-decoration: none;
            font-weight: 500; font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .menu-link:hover {
            color: var(--sidebar-item-hover);
            background: rgba(255, 255, 255, 0.04);
        }
        .menu-link.active {
            color: var(--sidebar-active-text);
            background: var(--sidebar-active-bg);
            box-shadow: inset 3px 0 0 var(--primary);
        }
        .menu-icon { font-size: 1.25rem; flex-shrink: 0; transition: transform 0.2s; }
        .menu-link:hover .menu-icon { transform: scale(1.1); }
        .menu-text { white-space: nowrap; flex: 1; }

        /* Submenu */
        .submenu { 
            background: transparent; 
            margin-top: 4px; 
            padding-left: 28px;
            position: relative;
        }
        .submenu::before {
            content: ''; position: absolute; left: 22px; top: 0; bottom: 12px;
            width: 1px; background: #334155;
        }
        .submenu .menu-link { 
            padding: 10px 16px; font-size: 0.9rem; color: #94a3b8;
        }
        .submenu .menu-link:hover { background: transparent; color: white; }
        .submenu .menu-link.active { background: transparent; color: white; font-weight: 600; box-shadow: none; }
        .submenu .menu-link.active::before {
            content: ''; position: absolute; left: -8px; top: 50%; transform: translateY(-50%);
            width: 5px; height: 5px; border-radius: 50%; background: var(--primary);
        }
        .menu-link[aria-expanded="true"] { color: white; }
        .menu-link[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
        .bi-chevron-down { transition: transform 0.3s ease; font-size: 0.8rem; }

        /* Sidebar Collapsed State */
        .app-sidebar.collapsed { width: var(--sidebar-w-collapsed); }
        .app-sidebar.collapsed .brand-text, 
        .app-sidebar.collapsed .menu-text, 
        .app-sidebar.collapsed .bi-chevron-down,
        .app-sidebar.collapsed .menu-label { display: none; }
        .app-sidebar.collapsed .menu-link { justify-content: center; padding: 14px 0; }
        .app-sidebar.collapsed .submenu { display: none !important; }
        .app-sidebar.collapsed .sidebar-brand { justify-content: center; padding: 0; }

        /* ================= MAIN CONTENT WRAPPER ================= */
        .app-main {
            flex: 1; min-width: 0;
            margin-left: var(--sidebar-w);
            display: flex; flex-direction: column; 
            transition: margin-left var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }
        .app-sidebar.collapsed + .app-main { margin-left: var(--sidebar-w-collapsed); }

        /* ================= HEADER ================= */
        .app-header {
            height: var(--header-h);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            position: sticky; top: 0; z-index: 1030;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
        }

        .header-left { display: flex; align-items: center; gap: 20px; }
        .toggle-sidebar-btn {
            background: var(--bg-app); border: 1px solid var(--border-color);
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); cursor: pointer; transition: all 0.2s;
        }
        .toggle-sidebar-btn:hover { background: var(--border-color); color: var(--text-main); }
        
        .page-title { font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; }

        .header-right { display: flex; align-items: center; gap: 24px; }
        
        .action-btn {
            position: relative; color: var(--text-muted); font-size: 1.25rem;
            cursor: pointer; transition: color 0.2s; background: none; border: none; padding: 0;
        }
        .action-btn:hover { color: var(--primary); }
        .badge-dot {
            position: absolute; top: 2px; right: 0;
            width: 10px; height: 10px; background: #ef4444; border-radius: 50%;
            border: 2px solid white;
        }

        /* User Profile Dropdown */
        .user-profile { 
            display: flex; align-items: center; gap: 12px; cursor: pointer;
            padding: 6px 12px; border-radius: 30px; transition: background 0.2s;
            border: 1px solid transparent;
        }
        .user-profile:hover { background: var(--bg-app); border-color: var(--border-color); }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--primary-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem;
        }
        .user-info { display: none; }
        @media (min-width: 768px) { .user-info { display: block; text-align: left; } }
        .user-name { font-weight: 700; font-size: 0.9rem; color: var(--text-main); line-height: 1.2; }
        .user-role { font-size: 0.75rem; color: var(--text-muted); font-weight: 500;}
        
        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 12px; padding: 8px 0; min-width: 220px;
        }
        .dropdown-item { padding: 10px 20px; font-weight: 500; font-size: 0.95rem; color: var(--text-main); }
        .dropdown-item:hover { background-color: var(--bg-app); color: var(--primary); }
        .dropdown-item i { font-size: 1.1rem; vertical-align: middle; }

        /* ================= CONTENT AREA ================= */
        .content-area { padding: 32px; flex: 1; display: flex; flex-direction: column; }

        /* ================= FOOTER ================= */
        .app-footer {
            padding: 24px 32px; text-align: center; color: var(--text-muted);
            font-size: 0.85rem; font-weight: 500; background: transparent;
            margin-top: auto;
        }

        /* ================= MOBILE OVERLAY ================= */
        .sidebar-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
            z-index: 1035; display: none; opacity: 0; transition: opacity 0.3s;
        }

        /* ================= MOBILE BOTTOM NAV ================= */
        .mobile-bottom-nav {
            display: none; position: fixed;
            bottom: var(--safe-bottom); left: 20px; right: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 24px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.12);
            z-index: 1020; height: var(--mob-bottom-nav-h);
            padding: 0 12px;
        }
        .mob-nav-container { display: flex; justify-content: space-between; align-items: center; height: 100%; }
        .mob-nav-item {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; 
            text-decoration: none; color: var(--text-muted);
            border-radius: 18px; height: 85%; transition: all 0.2s;
        }
        .mob-nav-item.active { color: var(--primary); background: var(--primary-light); }
        .mob-nav-icon { font-size: 1.4rem; margin-bottom: 2px; }
        .mob-nav-text { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px; }

        /* ================= RESPONSIVE DESIGN ================= */
        /* Tablet / Small Desktop - Auto Collapse Sidebar */
        @media (max-width: 1199.98px) and (min-width: 992px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.show { transform: translateX(0); box-shadow: 15px 0 30px rgba(0,0,0,0.15); }
            .app-main { margin-left: 0 !important; padding-bottom: calc(var(--mob-bottom-nav-h) + var(--safe-bottom) + 20px); }
            .app-header { padding: 0 20px; }
            .content-area { padding: 20px; }
            .mobile-bottom-nav { display: block; }
            .app-footer { display: none; }
            .sidebar-overlay.show { display: block; opacity: 1; }
            .user-profile { padding: 0; border: none; }
            .user-profile:hover { background: transparent; }
        }

        /* Mobile Screens */
        @media (max-width: 991.98px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.show { transform: translateX(0); box-shadow: 15px 0 30px rgba(0,0,0,0.15); }
            .app-main { margin-left: 0 !important; padding-bottom: calc(var(--mob-bottom-nav-h) + var(--safe-bottom) + 20px); }
            .app-header { padding: 0 20px; }
            .content-area { padding: 20px; }
            .mobile-bottom-nav { display: block; }
            .app-footer { display: none; }
            .sidebar-overlay.show { display: block; opacity: 1; }
            .user-profile { padding: 0; border: none; }
            .user-profile:hover { background: transparent; }
        }
    </style>
    @stack('styles')
</head>
<body>

    @include('sweetalert::alert')

    <!-- LOADER -->
    <div id="app-loader">
        <div class="spinner-ring"></div>
        <div class="loader-text">Menyiapkan Workspace...</div>
    </div>

    <!-- MOBILE OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="app-sidebar" id="appSidebar">
        <a href="{{ route('admin.index') }}" class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-lightning-fill"></i></div>
            <div class="brand-text">Twincomgo</div>
        </a>

        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            
            @php
                $navItems = [
                    ['route' => 'admin.index', 'icon' => 'bi-grid', 'label' => 'Dashboard'],
                    ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Pengguna'],
                    ['route' => 'admin.items', 'icon' => 'bi-box-seam', 'label' => 'Barang & Jasa'],
                    ['route' => 'admin.log', 'icon' => 'bi-clock-history', 'label' => 'Log Aktivitas'],
                    ['route' => 'customer.index', 'icon' => 'bi-person-vcard', 'label' => 'Customer'],
                    [
                        'icon' => 'bi-shop', 'label' => 'Galeri Second',
                        'children' => [
                            ['route' => 'second.index', 'label' => 'Pengajuan Harga'],
                            ['route' => 'second.product', 'label' => 'Daftar Barang'],
                        ]
                    ],
                    [
                        'icon' => 'bi-motherboard', 'label' => 'Simulasi',
                        'children' => [
                            ['route' => 'admin.simulasi.rakitpc', 'label' => 'Rakit PC'],
                            ['route' => 'admin.simulasi.rakitcctv', 'label' => 'Rakit CCTV'],
                        ]
                    ],
                ];
                
                $settingItems = [
                    [
                        'icon' => 'bi-gear', 'label' => 'Pengaturan',
                        'children' => [
                            ['route' => 'permission.index', 'label' => 'Hak Akses'],
                            ['route' => 'aa.index', 'label' => 'Accurate Token'],
                        ]
                    ],
                ];
            @endphp

            @foreach ($navItems as $index => $item)
                <div class="menu-item">
                    @if(isset($item['children']))
                        <a href="#submenu-main-{{ $index }}" class="menu-link" data-bs-toggle="collapse" role="button" aria-expanded="false">
                            <i class="menu-icon {{ $item['icon'] }}"></i>
                            <span class="menu-text">{{ $item['label'] }}</span>
                            <i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse submenu" id="submenu-main-{{ $index }}">
                            @foreach ($item['children'] as $child)
                                <a href="{{ route($child['route']) }}" class="menu-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                    <span class="menu-text">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="menu-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <i class="menu-icon {{ $item['icon'] }}"></i>
                            <span class="menu-text">{{ $item['label'] }}</span>
                        </a>
                    @endif
                </div>
            @endforeach

            <div class="menu-label">Sistem</div>
            @foreach ($settingItems as $index => $item)
                <div class="menu-item">
                    <a href="#submenu-sys-{{ $index }}" class="menu-link" data-bs-toggle="collapse" role="button" aria-expanded="false">
                        <i class="menu-icon {{ $item['icon'] }}"></i>
                        <span class="menu-text">{{ $item['label'] }}</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse submenu" id="submenu-sys-{{ $index }}">
                        @foreach ($item['children'] as $child)
                            <a href="{{ route($child['route']) }}" class="menu-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                <span class="menu-text">{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="app-main">
        <!-- HEADER -->
        <header class="app-header">
            <div class="header-left">
                <button class="toggle-sidebar-btn" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="header-right">
                <button class="action-btn">
                    <i class="bi bi-bell"></i>
                    <span class="badge-dot"></span>
                </button>

                <div class="dropdown">
                    <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="user-role">Super Admin</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted d-none d-md-block ms-1" style="font-size: 0.8rem;"></i>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end mt-2 animate__animated animate__fadeIn animate__faster">
                        <li><h6 class="dropdown-header text-uppercase fw-bold text-muted">Akun Anda</h6></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2 text-muted"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2 text-muted"></i> Pengaturan</a></li>
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger fw-bold">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar Aplikasi
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- CONTENT AREA -->
        <main class="content-area animate__animated animate__fadeIn animate__faster">
            @yield('content')
        </main>

        <!-- FOOTER (Desktop Only) -->
        {{-- <footer class="app-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span>&copy; {{ date('Y') }} <strong>Twincom Group</strong>. All rights reserved.</span>
                <span class="text-muted">Version 2.0.0</span>
            </div>
        </footer> --}}
    </div>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="mobile-bottom-nav">
        <div class="mob-nav-container">
            <a href="{{ route('admin.index') }}" class="mob-nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                <i class="mob-nav-icon bi bi-grid"></i>
                <span class="mob-nav-text">Home</span>
            </a>
            <a href="{{ route('admin.items') }}" class="mob-nav-item {{ request()->routeIs('admin.items') ? 'active' : '' }}">
                <i class="mob-nav-icon bi bi-box-seam"></i>
                <span class="mob-nav-text">Barjas</span>
            </a>
            <a href="{{ route('second.index') }}" class="mob-nav-item {{ request()->routeIs('second.index') ? 'active' : '' }}">
                <i class="mob-nav-icon bi bi-tags"></i>
                <span class="mob-nav-text">Harga</span>
            </a>
            <a href="{{ route('customer.index') }}" class="mob-nav-item {{ request()->routeIs('customer.index') ? 'active' : '' }}">
                <i class="mob-nav-icon bi bi-person-vcard"></i>
                <span class="mob-nav-text">Customer</span>
            </a>
            <a href="#" class="mob-nav-item" data-bs-toggle="modal" data-bs-target="#mobileMenuModal">
                <i class="mob-nav-icon bi bi-list"></i>
                <span class="mob-nav-text">Lainnya</span>
            </a>
        </div>
    </nav>

    <!-- MOBILE MENU MODAL (For extra menus on mobile) -->
    <div class="modal fade" id="mobileMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Menu Lengkap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-4">
                        <div class="avatar">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
                        <div>
                            <div class="fw-bold fs-6">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="text-muted small fw-medium">Super Admin</div>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('admin.simulasi.rakitpc') }}" class="btn btn-light w-100 py-3 rounded-4 text-start shadow-sm border-0 d-flex flex-column gap-2">
                                <i class="bi bi-pc-display text-primary fs-4"></i>
                                <span class="fw-bold text-dark fs-7">Rakit PC</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.simulasi.rakitcctv') }}" class="btn btn-light w-100 py-3 rounded-4 text-start shadow-sm border-0 d-flex flex-column gap-2">
                                <i class="bi bi-camera-video text-primary fs-4"></i>
                                <span class="fw-bold text-dark fs-7">Rakit CCTV</span>
                            </a>
                        </div>
                        <div class="col-12 mt-3">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger-subtle text-danger w-100 py-3 rounded-4 fw-bold border-0">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // --- Service Worker PWA ---
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/serviceworker.js').catch(err => {
                    console.log('PWA SW registration failed: ', err);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // --- Sidebar Toggle Logic ---
            const sidebar = document.getElementById('appSidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            // Cek state desktop dari localStorage (Diterapkan untuk lebar layar >= 1200px)
            if (window.innerWidth >= 1200) {
                if (localStorage.getItem('sidebar_collapsed') === 'true') {
                    sidebar.classList.add('collapsed');
                }
            }

            // Fungsi Toggle
            toggleBtn.addEventListener('click', () => {
                if (window.innerWidth >= 992) {
                    // Desktop: Collapse behavior
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
                } else {
                    // Mobile: Offcanvas behavior
                    sidebar.classList.add('show');
                    overlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            });

            // Tutup sidebar mobile saat overlay diklik
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            });

            // --- Loader Logic ---
            const loader = document.getElementById('app-loader');
            let loaderTimeout;

            function showLoader() {
                if (loaderTimeout) clearTimeout(loaderTimeout);
                loader.style.display = 'flex';
                // Trigger reflow for fade in
                loader.style.opacity = '0';
                setTimeout(() => loader.style.opacity = '1', 10);
            }

            function hideLoader() {
                loader.style.opacity = '0';
                loaderTimeout = setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            }

            // Intercept Clicks & Forms
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (e.defaultPrevented || (link && (link.hasAttribute('data-ajax') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-no-loader')))) return;
                
                if (link && link.href) {
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('#') && link.getAttribute('target') !== '_blank') {
                        const url = new URL(href, window.location.origin);
                        if (!(url.pathname === window.location.pathname && url.hash)) {
                            showLoader();
                        }
                    }
                }
            });

            document.addEventListener('submit', function(e) {
                if (!e.defaultPrevented) showLoader();
            });

            // Failsafe & Event Listeners
            window.addEventListener('pageshow', hideLoader);
            window.addEventListener('load', hideLoader);
            setTimeout(hideLoader, 2000); 
            
            // --- Error Handling (SweetAlert) ---
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#4f46e5',
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            @endif
        });
    </script>
    
    @stack('scripts')
</body>
</html>