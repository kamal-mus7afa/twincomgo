<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Twincomgo - Admin Panel</title>
    <link rel="icon" href="{{ asset('images/tw.png') }}" type="image/png">

    {{-- Library CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    {{-- Custom CSS --}}
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-darker: #115e59;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 80px;
            --mobile-header-height: 70px;
            --safe-area-top: env(safe-area-inset-top, 0px);
            --safe-area-bottom: env(safe-area-inset-bottom, 0px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d9488 0%, #115e59 100%);
            overflow-x: hidden;
        }

        body {
            display: flex;
            position: relative;
            padding-top: var(--safe-area-top);
            padding-bottom: var(--safe-area-bottom);
        }

        /* ===== LOADER STYLING YANG DIPERBAIKI ===== */
        #loader-display {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: opacity 0.3s ease;
        }

        .loader-container {
            text-align: center;
            transform: translateY(-20px);
            animation: loaderFadeIn 0.5s ease forwards;
        }

        .loader-spinner {
            width: 70px;
            height: 70px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid var(--primary);
            border-right: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
            box-shadow: 0 0 20px rgba(13, 148, 136, 0.3);
        }

        .loader-text {
            color: white;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 1px;
            margin-top: 15px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .loader-progress {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 20px auto 0;
            overflow: hidden;
            position: relative;
        }

        .loader-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            animation: progress 1.5s ease infinite;
        }

        @keyframes loaderFadeIn {
            from {
                opacity: 0;
                transform: translateY(0);
            }
            to {
                opacity: 1;
                transform: translateY(-20px);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        /* ===== Glass Sidebar ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            height: 100vh;
            z-index: 1002;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            transform: translateX(-100%);
            will-change: transform;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            min-height: var(--mobile-header-height);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0d9488, #115e59);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .sidebar-footer {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 14px 0;
        }

        .sidebar.collapsed .nav-icon {
            margin: 0;
            font-size: 20px;
        }

        /* ===== Navigation ===== */
        .sidebar-nav {
            flex: 1;
            padding: 15px 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nav-item {
            margin: 5px 10px;
            border-radius: 12px;
            overflow: hidden;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 12px;
            font-size: 15px;
        }

        .nav-link.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            box-shadow: 0 4px 15px rgba(13, 148, 136, 0.4);
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .nav-text {
            font-weight: 500;
            white-space: nowrap;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== Main Wrapper ===== */
        .main-wrapper {
            flex: 1;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            width: 100%;
            position: relative;
            overflow-x: hidden;
        }

        /* ===== Mobile Header ===== */
        .mobile-header {
            display: none;
            height: var(--mobile-header-height);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 0 15px;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: var(--safe-area-top);
            left: 0;
            right: 0;
            z-index: 1001;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .mobile-menu-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-title {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }

        .mobile-user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        /* ===== Glass Header (Desktop) ===== */
        .navbar-admin {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toggle-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* ===== User Menu ===== */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-bell {
            position: relative;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--dark);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
        }

        .user-role {
            font-size: 12px;
            color: var(--secondary);
        }

        /* ===== Main Content ===== */
        main {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 20px 15px;
            background: transparent;
            padding-top: calc(var(--mobile-header-height) + 20px);
        }

        .content-wrapper {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            margin-bottom: 20px;
        }

        /* ===== Mobile Bottom Navigation ===== */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 1000;
            padding: 10px 0 var(--safe-area-bottom);
            height: 70px;
        }

        .bottom-nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 100%;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--secondary);
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            flex: 1;
            max-width: 80px;
        }

        .bottom-nav-item.active {
            color: var(--primary);
            background: rgba(13, 148, 136, 0.1);
        }

        .bottom-nav-icon {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .bottom-nav-text {
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
        }

        /* ===== Footer ===== */
        footer {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--secondary);
            text-align: center;
            padding: 15px;
            font-size: 13px;
        }

        /* ===== Overlay for Mobile Sidebar ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.5s ease-out;
        }

        /* ===== Floating Elements ===== */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            left: -50px;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: -30px;
            right: -30px;
        }

        /* ===== Responsive Breakpoints ===== */
        /* Tablet & Desktop */
        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0) !important;
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-collapsed);
            }
            
            .main-wrapper {
                margin-left: var(--sidebar-width);
            }
            
            .sidebar.collapsed ~ .main-wrapper {
                margin-left: var(--sidebar-collapsed);
            }
            
            .mobile-header,
            .mobile-bottom-nav,
            .sidebar-overlay {
                display: none !important;
            }
            
            .navbar-admin {
                display: flex;
                position: relative;
                z-index: 1050;
            }
            
            main {
                padding-top: 30px;
                padding-left: 30px;
                padding-right: 30px;
            }
            
            .sidebar.collapsed .nav-text,
            .sidebar.collapsed .logo-text,
            .sidebar.collapsed .sidebar-footer {
                display: none;
            }

            .sidebar.collapsed .nav-link {
                justify-content: center;
                padding: 14px 0;
            }

            .sidebar.collapsed .nav-icon {
                margin: 0;
                font-size: 20px;
            }

            .sidebar.collapsed .nav-badge {
                display: none;
            }

            .sidebar.collapsed {
                width: var(--sidebar-collapsed);
            }

            .sidebar.collapsed ~ .main-wrapper {
                margin-left: var(--sidebar-collapsed);
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .mobile-header {
                display: flex;
            }
            
            .mobile-bottom-nav {
                display: block;
            }
            
            .navbar-admin {
                display: none;
            }
            
            .main-wrapper {
                margin-left: 0 !important;
                padding-bottom: 70px;
            }
            
            .sidebar {
                width: 85%;
                max-width: 320px;
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-header {
                padding-top: calc(var(--safe-area-top) + 15px);
            }
            
            .logo-text {
                font-size: 16px;
            }
            
            .nav-link {
                padding: 12px 15px;
                font-size: 14px;
            }
            
            .content-wrapper {
                padding: 15px;
                border-radius: 14px;
            }
            
            .user-info {
                display: none;
            }
            
            .dropdown-menu {
                position: fixed !important;
                top: auto !important;
                bottom: 80px !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: 90% !important;
                max-width: 300px;
                z-index: 1060;
            }
            
            footer {
                padding: 12px 15px;
                font-size: 12px;
            }
            
            .footer-mobile {
                display: block;
                text-align: center;
                padding: 10px;
                font-size: 11px;
                color: var(--secondary);
                margin-top: 10px;
            }
            
            .sidebar.collapsed {
                width: 85%;
                max-width: 320px;
            }
            
            .sidebar.collapsed .nav-text,
            .sidebar.collapsed .logo-text,
            .sidebar.collapsed .sidebar-footer {
                display: block;
            }
            
            .sidebar.collapsed .nav-link {
                justify-content: flex-start;
                padding: 12px 15px;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            .mobile-title {
                font-size: 16px;
            }
            
            .mobile-menu-btn,
            .mobile-user-avatar {
                width: 40px;
                height: 40px;
            }
            
            .content-wrapper {
                padding: 12px;
            }
            
            main {
                padding: calc(var(--mobile-header-height) + 10px) 12px 20px;
            }
            
            .bottom-nav-text {
                font-size: 10px;
            }
            
            .bottom-nav-icon {
                font-size: 18px;
            }
        }

        /* Landscape Mode */
        @media (max-height: 600px) and (orientation: landscape) {
            .mobile-bottom-nav {
                display: none;
            }
            
            main {
                padding-bottom: 20px;
            }
            
            .sidebar-nav {
                max-height: 60vh;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- Floating Background Elements --}}
<div class="floating-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
</div>

{{-- LOADER DISPLAY --}}
<div id="loader-display">
    <div class="loader-container">
        <div class="loader-spinner"></div>
        <div class="loader-text">Loading...</div>
        <div class="loader-progress">
            <div class="loader-progress-bar"></div>
        </div>
    </div>
</div>

{{-- Mobile Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Mobile Header --}}
<div class="mobile-header">
    <button class="mobile-menu-btn" id="mobileToggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    <div class="mobile-title">@yield('page-title', 'Dashboard')</div>
    <div class="mobile-user-avatar" onclick="toggleUserMenu()">
        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
    </div>
</div>

{{-- Glass Sidebar --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="logo">
            <div class="logo-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>
            <div class="logo-text">Twincomgo</div>
        </a>
    </div>

    <nav class="sidebar-nav">
        @php
            $navItems = [
                ['route' => 'admin.index', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'badge' => null],
                ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Pengguna', 'badge' => null],
                ['route' => 'admin.items', 'icon' => 'bi-box-seam', 'label' => 'Barang & Jasa', 'badge' => null],
                ['route' => 'admin.log', 'icon' => 'bi-archive', 'label' => 'Log', 'badge' => null],
                ['route' => 'admin.galeri.index', 'icon' => 'bi-shop', 'label' => 'Galeri Second', 'badge' => null],
                [
                    'icon' => 'bi-toggles',
                    'label' => 'Simulasi',
                    'children' => [
                        ['route' => 'admin.simulasi.rakitpc', 'icon' => 'bi-pc-display', 'label' => 'Rakit PC'],
                        ['route' => 'admin.simulasi.rakitcctv', 'icon' => 'bi-camera-video', 'label' => 'Rakit CCTV'],
                    ]
                ],
                ['route' => 'aa.index', 'icon' => 'bi-diagram-3', 'label' => 'Accurate Token', 'badge' => null],
            ];
        @endphp

        @foreach ($navItems as $item)
            <div class="nav-item">

                @if(isset($item['children']))
                    <!-- Dropdown -->
                    <div class="">
                        <a class="nav-link"
                        data-bs-toggle="collapse"
                        href="#menuSimulasi"
                        role="button"
                        aria-expanded="false">

                            <i class="nav-icon bi {{ $item['icon'] }}"></i>
                            <span class="nav-text">{{ $item['label'] }}</span>
                            <i class="bi bi-chevron-down ms-auto"></i>
                        </a>

                        <div class="collapse" id="menuSimulasi">
                            <div class="ps-4">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}" class="nav-link">
                                        <i class="nav-icon bi {{ $child['icon'] }}"></i>
                                        <span class="nav-text">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                @else
                    <!-- Normal Menu -->
                    <a href="{{ route($item['route']) }}"
                    class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <i class="nav-icon bi {{ $item['icon'] }}"></i>
                        <span class="nav-text">{{ $item['label'] }}</span>
                    </a>
                @endif

            </div>
        @endforeach
    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer p-3 border-top border-white-10">
        <div class="text-center text-white-60 small">
            <i class="bi bi-shield-check me-1"></i>
            Secure Admin Panel
        </div>
    </div>
</aside>

{{-- Main Content Area --}}
<div class="main-wrapper">
    {{-- Glass Header (Desktop) --}}
    <nav class="navbar-admin">
        <div class="header-left">
            <button class="toggle-btn" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="page-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>
        </div>

        <div class="user-menu">
            <div class="notification-bell">
                <i class="bi bi-bell"></i>
                <span class="notification-badge">3</span>
            </div>
            
            <div class="dropdown">
                <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
                        <div class="user-role">Super Admin</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        <div class="content-wrapper animate-fade-in-up">
            @yield('content')
        </div>
    </main>

    {{-- Footer (Desktop) --}}
    <footer class="d-none d-md-block">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start text-center">
                    <small>© {{ date('Y') }} Twincom Group — All rights reserved</small>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Secure Admin Panel v2.0
                    </small>
                </div>
            </div>
        </div>
    </footer>
</div>

{{-- Mobile Bottom Navigation --}}
<div class="mobile-bottom-nav">
    <div class="bottom-nav-container">
        @php
            $bottomNavItems = [
                ['route' => 'admin.index', 'icon' => 'bi-speedometer2', 'label' => 'Home'],
                ['route' => 'admin.user', 'icon' => 'bi-people', 'label' => 'Users'],
                ['route' => 'admin.items', 'icon' => 'bi-box-seam', 'label' => 'Barjas'],
                ['route' => 'admin.log', 'icon' => 'bi-archive', 'label' => 'Logs'],
                ['route' => 'aa.index', 'icon' => 'bi-diagram-3', 'label' => 'Token'],
            ];
        @endphp
        
        @foreach ($bottomNavItems as $item)
            <a href="{{ route($item['route']) }}" 
               class="bottom-nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}"
               onclick="showLoader()">
                <i class="bottom-nav-icon bi {{ $item['icon'] }}"></i>
                <span class="bottom-nav-text">{{ $item['label'] }}</span>
            </a>
        @endforeach
        
        <a href="#" class="bottom-nav-item" onclick="toggleUserMenu()">
            <i class="bottom-nav-icon bi bi-person"></i>
            <span class="bottom-nav-text">Logout</span>
        </a>
    </div>
</div>

{{-- Mobile User Menu Modal --}}
<div class="modal fade" id="mobileUserMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-bottom">
        <div class="modal-content border-0 rounded-top-3">
            <div class="modal-header border-0">
                <h5 class="modal-title">Menu Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action border-0 py-3">
                        <i class="bi bi-person me-3"></i>Profile
                    </a>
                    <a href="#" class="list-group-item list-group-item-action border-0 py-3">
                        <i class="bi bi-gear me-3"></i>Settings
                    </a>
                    <div class="list-group-item border-0 py-3">
                        <form action="{{ route('logout') }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
    // Mobile sidebar functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileToggleBtn = document.getElementById('mobileToggleSidebar');
    const desktopToggleBtn = document.getElementById('toggleSidebar');
    const loader = document.getElementById('loader-display');
    let loaderTimeout;

    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleSidebar() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Mobile toggle
    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', toggleSidebar);
    }

    // ===== SIDEBAR STATE MANAGEMENT =====
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const desktopToggleBtn = document.getElementById('toggleSidebar');
        
        // Nonaktifkan transisi sementara untuk menghindari flicker
        sidebar.classList.add('no-transition');
        
        // Cek apakah ada state tersimpan
        const sidebarState = localStorage.getItem('sidebar_collapsed');
        
        // Terapkan state yang tersimpan (hanya untuk desktop)
        if (window.innerWidth >= 769) {
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
        
        // Aktifkan kembali transisi setelah state diterapkan
        setTimeout(() => {
            sidebar.classList.remove('no-transition');
        }, 100);
        
        // Toggle sidebar dan simpan state
        if (desktopToggleBtn) {
            // Hapus event listener lama untuk menghindari duplikasi
            desktopToggleBtn.removeEventListener('click', function() {});
            
            desktopToggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                // Simpan state ke localStorage (hanya untuk desktop)
                if (window.innerWidth >= 769) {
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebar_collapsed', isCollapsed);
                }
            });
        }
        
        // Reset state saat resize dari mobile ke desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 769) {
                const savedState = localStorage.getItem('sidebar_collapsed');
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                }
            } else {
                // Di mobile, collapsed tidak berlaku
                sidebar.classList.remove('collapsed');
            }
        });
    });

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar when pressing ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Mobile user menu
    function toggleUserMenu() {
        const userMenu = new bootstrap.Modal(document.getElementById('mobileUserMenu'));
        userMenu.show();
    }

    // ===== LOADER FUNCTIONALITY =====
    function showLoader() {
        if (loaderTimeout) clearTimeout(loaderTimeout);
        loader.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function hideLoader() {
        loaderTimeout = setTimeout(() => {
            loader.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }

    // Handle link clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        const clickableRow = e.target.closest('[onclick*="window.location"]');
        
        // Jika bukan link dan bukan row yang bisa diklik, return
        if (!link && !clickableRow) return;
        
        // CEK APAKAH INI AJAX (dengan melihat apakah event dicegah defaultnya)
        // atau link memiliki attribute data-ajax / data-no-loader
        if (e.defaultPrevented || 
            (link && (link.hasAttribute('data-ajax') || link.hasAttribute('data-no-loader')))) {
            return; // Ini AJAX, jangan trigger loader
        }
        
        // Jika link memiliki href yang valid dan bukan anchor
        if (link) {
            const href = link.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('#') && 
                link.getAttribute('target') !== '_blank') {
                
                // Cek apakah link ke halaman yang sama (anchor)
                if (href.startsWith(window.location.origin) || href.startsWith('/')) {
                    const url = new URL(href, window.location.origin);
                    if (url.pathname === window.location.pathname && url.hash) {
                        return; // Anchor di halaman yang sama
                    }
                }
                
                showLoader();
            }
        }
        
        // Jika row yang diklik (onclick navigasi)
        if (clickableRow) {
            showLoader();
        }
    });

    // Handle form submissions
    document.addEventListener('submit', function(e) {
        // Jika form tidak menggunakan AJAX (tidak ada preventDefault)
        if (!e.defaultPrevented) {
            showLoader();
        }
    });

    // Handle page load selesai
    window.addEventListener('load', function() {
        hideLoader();
    });
    
    // Handle back/forward navigation
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            hideLoader();
        }
    });

    // Fallback untuk memastikan loader hilang
    window.addEventListener('beforeunload', function() {
        // Clear timeout saat page ditinggalkan
        if (loaderTimeout) {
            clearTimeout(loaderTimeout);
        }
    });

    // Hide loader setelah 5 detik maksimal (fallback)
    setTimeout(hideLoader, 5000);

    // Swipe to close sidebar on mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, false);

    document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        if (sidebar.classList.contains('active') && touchStartX - touchEndX > 50) {
            closeSidebar();
        }
    }, false);

    // Handle mobile viewport height
    function setVH() {
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);

    // Prevent zoom on double tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Handle iOS safe area
    function setSafeArea() {
        document.documentElement.style.setProperty('--safe-area-top', getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-top)') || '0px');
        document.documentElement.style.setProperty('--safe-area-bottom', getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-bottom)') || '0px');
    }

    if (CSS.supports('padding-top: env(safe-area-inset-top)')) {
        setSafeArea();
        window.addEventListener('resize', setSafeArea);
    }
</script>

@stack('scripts')
</body>
</html>