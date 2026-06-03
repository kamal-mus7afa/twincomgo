@extends('layouts.admin')

@section('title', 'Pengguna')

@section('page-title', 'Pengguna')

@section('content')

@push('styles')
{{-- Jika Anda masih menggunakan user.css, pastikan tidak ada gaya yang bentrok. 
     Sebaiknya gunakan gaya premium di bawah ini. --}}
<!-- <link rel="stylesheet" href="{{ asset('css/admin/user.css') }}"> -->

<style>
    /* ===== PREMIUM SAAS DESIGN SYSTEM ===== */
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --primary-light: #e0e7ff;
        
        --success: #10b981;
        --success-light: #d1fae5;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --info: #0ea5e9;
        --info-light: #e0f2fe;
        --purple: #8b5cf6;
        --purple-light: #ede9fe;
        
        --dark: #0f172a;
        --secondary: #64748b;
        --bg-surface: #ffffff;
        --bg-light: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }
    .header-content h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 6px;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .page-description {
        color: var(--secondary);
        margin: 0;
        font-size: 14.5px;
    }
    .add-user-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .add-user-btn:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    .stat-item {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .stat-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: var(--dark);
        line-height: 1.1;
        margin-bottom: 8px;
    }
    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Stat Variations by Index */
    .stat-item:nth-child(1) .stat-number { color: var(--primary); }
    .stat-item:nth-child(2) .stat-number { color: var(--info); }
    .stat-item:nth-child(3) .stat-number { color: var(--warning); }
    .stat-item:nth-child(4) .stat-number { color: var(--purple); }

    /* ===== PANELS (FILTER & TABLE) ===== */
    .filter-card, .table-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .filter-header, .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: var(--bg-surface);
    }
    .filter-title, .table-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* ===== MODERN FORM (FILTER) ===== */
    .filter-card form {
        padding: 24px;
        background: var(--bg-light);
    }
    .form-label {
        font-size: 13px;
        color: var(--secondary);
        margin-bottom: 8px;
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .input-group-text {
        border-radius: 10px 0 0 10px;
        border-color: var(--border-color);
    }
    .search-btn {
        background: var(--dark);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 16px;
        transition: all 0.2s;
    }
    .search-btn:hover { background: #1e293b; color: white; }
    .reset-btn {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 16px;
        transition: all 0.2s;
    }
    .reset-btn:hover { background: #f1f5f9; color: var(--dark); }

    /* ===== MODERN TABLE ===== */
    .table-container { width: 100%; overflow-x: auto; }
    .table { margin: 0; width: 100%; }
    .table th {
        background: var(--bg-light);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .table td {
        padding: 16px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        transition: background 0.2s;
    }
    .table tbody tr:hover td { background: var(--bg-light); }
    .table tbody tr:last-child td { border-bottom: none; }

    /* User Avatar */
    .user-avatar-sm {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* Badges */
    .status-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    .badge-karyawan { background: var(--info-light); color: var(--info); border: 1px solid rgba(14,165,233,0.2); }
    .badge-reseller { background: var(--warning-light); color: #d97706; border: 1px solid rgba(245,158,11,0.2); }
    .badge-admin { background: var(--purple-light); color: var(--purple); border: 1px solid rgba(139,92,246,0.2); }
    .badge-other { background: #f1f5f9; color: var(--secondary); border: 1px solid var(--border-color); }

    /* Action Buttons */
    .action-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; font-size: 15px; transition: all 0.2s;
    }
    .btn-view { background: var(--info-light); color: var(--info); }
    .btn-view:hover { background: var(--info); color: white; transform: translateY(-2px); }
    
    .btn-edit { background: var(--primary-light); color: var(--primary); }
    .btn-edit:hover { background: var(--primary); color: white; transform: translateY(-2px); }
    
    .btn-delete { background: var(--danger-light); color: var(--danger); }
    .btn-delete:hover { background: var(--danger); color: white; transform: translateY(-2px); }
    
    .btn-update { background: var(--purple-light); color: var(--purple); }
    .btn-update:hover { background: var(--purple); color: white; transform: translateY(-2px); }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 3rem; color: #cbd5e1; margin-bottom: 16px; }

    /* Animations */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.5s ease backwards; }
</style>
@endpush

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
            <div class="header-content">
                <h1>Kelola Pengguna</h1>
                <p class="page-description">Kelola dan pantau semua akun pengguna di sistem Anda.</p>
            </div>
            <a href="{{ route('users2.create') }}" class="btn add-user-btn">
                <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('err'))
        <div class="alert alert-danger alert-dismissible fade show animate-card border-0 rounded-4 shadow-sm" style="animation-delay: 0.1s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-octagon-fill me-3 fs-5"></i>
                <div class="fw-medium flex-grow-1">{{ session('err') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-card border-0 rounded-4 shadow-sm" style="animation-delay: 0.1s" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                <div class="fw-medium flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.15s">
        <div class="stat-item" onclick="window.location='{{ route('admin.user') }}'">
            <div class="stat-number">{{ number_format($totalUsers) }}</div>
            <div class="stat-label"><i class="bi bi-people-fill me-1"></i> Total Users</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'karyawan']) }}'">
            <div class="stat-number">{{ number_format($totalKaryawan ?? 0) }}</div>
            <div class="stat-label"><i class="bi bi-person-vcard me-1"></i> Karyawan</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'">
            <div class="stat-number">{{ number_format($totalReseller ?? 0) }}</div>
            <div class="stat-label"><i class="bi bi-shop me-1"></i> Reseller</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'twincom patner']) }}'">
            <div class="stat-number">{{ number_format($totalTwincomPatner ?? 0) }}</div>
            <div class="stat-label"><i class="bi bi-shop me-1"></i> Twincom Patner</div>
        </div>
        <div class="stat-item" onclick="window.location='{{ route('admin.user', ['status' => 'admin']) }}'">
            <div class="stat-number">{{ number_format($totalAdmin ?? 0) }}</div>
            <div class="stat-label"><i class="bi bi-shield-lock me-1"></i> Admin</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="filter-card animate-card" style="animation-delay: 0.2s">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="bi bi-funnel-fill text-secondary"></i> Filter & Pencarian
            </h3>
        </div>
        
        <form method="GET" action="{{ route('admin.user') }}">
            <div class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="status" class="form-label fw-bold text-dark">Status Pengguna</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Pengguna</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('status') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="twincom patner" {{ request('status') == 'twincom patner' ? 'selected' : '' }}>Twincom Partner</option>
                    </select>
                </div>

                <div class="col-xl-5 col-lg-6 col-md-8">
                    <label for="search" class="form-label fw-bold text-dark">Cari Berdasarkan Nama/Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control border-start-0 ps-0" 
                               placeholder="Ketik kata kunci..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <label class="form-label fw-bold text-dark">Total Hasil</label>
                    <div class="form-control text-center fw-bold text-primary" style="background-color: var(--primary-light); border-color: transparent;">
                        @php
                            $total = 0;
                            if (request('status') === 'karyawan') $total = $totalKaryawan;
                            elseif (request('status') === 'reseller') $total = $totalReseller;
                            elseif (request('status') === 'admin') $total = $totalAdmin;
                            elseif (request('status') === 'twincom patner') $total = $totalTwincomPatner;
                            else $total = $totalUsers;
                        @endphp
                        {{ number_format($total) }}
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn search-btn flex-fill">
                            Cari
                        </button>
                        <a href="{{ route('admin.user') }}" class="btn reset-btn" data-bs-toggle="tooltip" title="Reset Filter">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card animate-card" style="animation-delay: 0.3s">
        <div class="table-header">
            <h3>
                <i class="bi bi-person-lines-fill text-primary"></i> Daftar Data Pengguna
            </h3>
        </div>

        @if (count($users) > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Informasi Pengguna</th>
                            <th class="text-center" width="150">Status / Role</th>
                            <th class="text-center" width="150">Kategori Penjualan</th>
                            <th class="text-center" width="180">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr>
                                <td class="text-center fw-bold text-muted">
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $user->name }}</div>
                                            <div class="text-muted small mt-1">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if (strtoupper($user->status) === 'KARYAWAN')
                                        <span class="status-badge badge-karyawan">Karyawan</span>
                                    @elseif (strtoupper($user->status) === 'RESELLER')
                                        <span class="status-badge badge-reseller">Reseller</span>
                                    @elseif (strtolower($user->status) === 'admin')
                                        <span class="status-badge badge-admin">Admin</span>
                                    @else
                                        <span class="status-badge badge-other">{{ $user->status ?? 'USER' }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if (strtoupper($user->kategori_penjualan) === 'KARYAWAN')
                                        <span class="status-badge badge-karyawan">Karyawan</span>
                                    @elseif (strtoupper($user->kategori_penjualan) === 'RESELLER')
                                        <span class="status-badge badge-reseller">Reseller</span>
                                    @elseif (strtolower($user->kategori_penjualan) === 'TWINCOM PATNER')
                                        <span class="status-badge badge-admin">TWINCOM PATNER</span>
                                    @else
                                        <span class="status-badge badge-other">{{ $user->kategori_penjualan}}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn action-btn btn-view" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('users2.edit', $user->id) }}" class="btn action-btn btn-edit" data-bs-toggle="tooltip" title="Edit Profil">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="{{ route('permission.edit', $user->id) }}" class="btn action-btn btn-update" data-bs-toggle="tooltip" title="Edit Hak Akses">
                                            <i class="bi bi-shield-lock-fill"></i>
                                        </a>
                                        <form method="POST" action="{{ route('users2.destroy', $user->id) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini secara permanen?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn action-btn btn-delete" data-bs-toggle="tooltip" title="Hapus Pengguna">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox empty-icon d-block"></i>
                <h4 class="fw-bold text-dark">Tidak Ada Pengguna Ditemukan</h4>
                <p class="text-muted mb-4">Pengguna yang Anda cari tidak ada atau filter tidak cocok.</p>
                <a href="{{ route('admin.user') }}" class="btn add-user-btn d-inline-flex">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reset Filter
                </a>
            </div>
        @endif
    </div>

</div>

@push('script')
@if(session('ok'))
    <script>
        // Menggunakan Toastify Hijau (Premium Look)
        Toastify({
            text: "<i class='bi bi-check-circle-fill me-2'></i> {{ session('ok') }}",
            duration: 4000,
            gravity: "top", 
            position: "right", 
            escapeMarkup: false,
            style: {
                background: "linear-gradient(to right, #10b981, #059669)", /* Emerald Green */
                borderRadius: "12px",
                boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)",
                fontWeight: "600",
                padding: "12px 20px"
            },
            stopOnFocus: true, 
        }).showToast();
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit status filter
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

        // Add loading state to search
        const form = document.querySelector('form');
        const searchBtn = form?.querySelector('button[type="submit"]');
        
        if (form && searchBtn) {
            form.addEventListener('submit', function() {
                searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Mencari...';
                searchBtn.disabled = true;
            });
        }
    });
</script>
@endpush
@endsection