@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('page-title', 'Log Aktivitas')

@section('content')

@push('styles')
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
        gap: 10px;
    }
    .page-description {
        color: var(--secondary);
        margin: 0;
        font-size: 14.5px;
    }
    .btn-back-pill {
        background: #fff;
        padding: 10px 20px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        color: var(--dark);
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-back-pill:hover {
        background: var(--bg-light);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .btn-back-pill i { color: var(--primary); font-size: 1.1rem; }

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
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        border-left: 4px solid var(--primary);
    }
    .stat-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .stat-item:nth-child(2) { border-left-color: var(--info); }
    .stat-item:nth-child(3) { border-left-color: var(--success); }
    .stat-item:nth-child(4) { border-left-color: var(--warning); }

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

    /* ===== PANELS (FILTER & TABLE) ===== */
    .filter-card, .table-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow: visible; /* Penting untuk dropdown & TomSelect */
    }
    .filter-header, .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: var(--bg-surface);
        border-radius: 16px 16px 0 0;
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
        border-radius: 0 0 16px 16px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
    }
    .form-control, .form-select, .date-filter-btn {
        border-radius: 10px;
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        font-size: 14px;
        background: #fff;
        transition: all 0.2s;
        color: var(--dark);
    }
    .form-control:focus, .form-select:focus, .date-filter-btn:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .date-filter-btn {
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .search-btn {
        background: var(--dark);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 16px;
        transition: all 0.2s;
        border: none;
    }
    .search-btn:hover { background: #1e293b; color: white; transform: translateY(-1px); }
    .reset-btn {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 16px;
        transition: all 0.2s;
    }
    .reset-btn:hover { background: #f1f5f9; color: var(--dark); }

    /* Customizing TomSelect to match Modern Theme */
    .ts-control {
        border-radius: 10px;
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        font-size: 14px;
        background: #fff;
        box-shadow: none;
        transition: all 0.2s;
    }
    .ts-control.focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .ts-dropdown { border-radius: 10px; border-color: var(--border-color); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .ts-dropdown .active { background-color: var(--bg-light); color: var(--dark); }

    /* ===== MODERN TABLE ===== */
    .table-container { 
        width: 100%; 
        overflow-x: auto; 
        max-height: 600px;
    }
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
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table td {
        padding: 16px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        transition: background 0.2s;
    }
    .table tbody tr:hover td { background: var(--bg-light); }
    
    /* Highlight Recent Activity */
    .table tbody tr.recent-activity td { background: rgba(14, 165, 233, 0.03); }
    .table tbody tr.recent-activity td:first-child { border-left: 3px solid var(--info); }

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

    /* Pagination */
    .pagination-section {
        padding: 20px 24px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        border-radius: 0 0 16px 16px;
    }
    .pagination-info { font-size: 13.5px; color: var(--secondary); }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 3rem; color: #cbd5e1; margin-bottom: 16px; }

    /* Dropdown overrides for Date picker */
    .dropdown-menu { border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

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
                <h1><i class="bi bi-clock-history text-primary me-2"></i> Log Aktivitas</h1>
                <p class="page-description">Pantau dan lacak semua aktivitas pengguna di sistem secara real-time.</p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn-back-pill">
                <i class="bi bi-arrow-left"></i> <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    {{-- STATS GRID --}}
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item">
            <div class="stat-number">{{ number_format($activities->total()) }}</div>
            <div class="stat-label"><i class="bi bi-activity me-1"></i> Total Aktivitas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ number_format($activities->where('created_at', '>=', now()->startOfDay())->count()) }}</div>
            <div class="stat-label"><i class="bi bi-calendar-event me-1"></i> Hari Ini</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">
                {{ number_format($activities->where('created_at', '>=', now()->startOfDay())->whereNotNull('causer_id')->unique('causer_id')->count()) }}
            </div>
            <div class="stat-label"><i class="bi bi-people-fill me-1"></i> User Aktif (Harian)</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ number_format($activities->where('created_at', '>=', now()->subHours(1))->count()) }}</div>
            <div class="stat-label"><i class="bi bi-stopwatch me-1"></i> 1 Jam Terakhir</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="filter-card animate-card" style="animation-delay: 0.2s">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="bi bi-funnel-fill text-secondary"></i> Filter & Pencarian
            </h3>
        </div>
        
        <form action="{{ route('admin.log') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                {{-- User Search --}}
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="user-search" class="form-label">Cari Pengguna</label>
                    <select id="user-search" name="user" class="form-select">
                        @if(request('user'))
                            <option value="{{ request('user') }}" selected>{{ request('user') }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="user" id="userId" value="{{ request('user') }}">
                </div>

                {{-- Status Filter --}}
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">Status Role</label>
                    <select name="status" class="form-select" id="status">
                        <option value="">Semua Status</option>
                        <option value="karyawan" {{ request('status') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="reseller" {{ request('status') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-xl-3 col-lg-5 col-md-6">
                    <label class="form-label">Rentang Tanggal</label>
                    <div class="dropdown w-100" data-bs-auto-close="outside">
                        <button class="btn date-filter-btn w-100" type="button" data-bs-toggle="dropdown">
                            <span>
                                <i class="bi bi-calendar3 text-muted me-2"></i>
                                @if(request('start_date') && request('end_date'))
                                    {{ request('start_date') }} s/d {{ request('end_date') }}
                                @else
                                    Semua Tanggal
                                @endif
                            </span>
                            <i class="bi bi-chevron-down ms-2" style="font-size: 12px;"></i>
                        </button>
                        <div class="dropdown-menu p-4 mt-1" style="min-width: 320px;">
                            <h6 class="mb-3 fw-bold text-dark">Filter Tanggal</h6>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" id="start_date" value="{{ request('start_date') }}" />
                            </div>
                            <div class="mb-4">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" id="end_date" value="{{ request('end_date') }}" />
                            </div>
                            <button type="submit" class="btn search-btn w-100 py-2">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <label for="search" class="form-label">Cari Aktivitas / Deskripsi</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control border-end-0" 
                               placeholder="Ketik kata kunci..." value="{{ request('search') }}">
                        <button class="btn search-btn border-start-0 px-3" type="submit" style="border-radius: 0;">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('admin.log') }}" class="btn reset-btn" data-bs-toggle="tooltip" title="Reset Semua Filter" style="border-radius: 0 10px 10px 0;">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.3s">
        <div class="table-header">
            <h3>
                <i class="bi bi-list-columns-reverse text-primary"></i>
                Riwayat Aktivitas
            </h3>
        </div>

        @if ($activities->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Informasi Pengguna</th>
                            <th width="180" class="text-center">Role / Status</th>
                            <th width="180" class="text-center">Tanggal</th>
                            <th width="140" class="text-center">Waktu Login</th>
                            <th width="140" class="text-center">Waktu Logout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $index => $activity)
                            <tr class="{{ now()->diffInMinutes($activity->created_at) <= 60 ? 'recent-activity' : '' }}">
                                <td class="text-center fw-bold text-muted">
                                    {{ $activities->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $activity->log_name ?? 'System' }}</div>
                                            @if(now()->diffInMinutes($activity->created_at) <= 60)
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small mt-1">Aktivitas Baru</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php $status = strtolower(optional($activity->causer)->status ?? 'other'); @endphp
                                    @if ($status === 'karyawan')
                                        <span class="status-badge badge-karyawan">Karyawan</span>
                                    @elseif ($status === 'reseller')
                                        <span class="status-badge badge-reseller">Reseller</span>
                                    @elseif($status === 'admin')
                                        <span class="status-badge badge-admin">Admin</span>
                                    @else
                                        <span class="status-badge badge-other">{{ $status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-medium text-secondary">{{ $activity->created_at->format('d M Y') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="text-success fw-bold bg-success bg-opacity-10 px-2 py-1 rounded d-inline-block">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> {{ $activity->created_at->format('H:i:s') }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($activity->logout_time)
                                        <div class="text-danger fw-bold bg-danger bg-opacity-10 px-2 py-1 rounded d-inline-block">
                                            <i class="bi bi-box-arrow-right me-1"></i> {{ $activity->logout_time->format('H:i:s') }}
                                        </div>
                                    @else
                                        <span class="text-muted"><i class="bi bi-dash-lg"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($activities->hasPages())
                <div class="pagination-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="pagination-info">
                            Menampilkan <strong>{{ $activities->firstItem() }}</strong> sampai <strong>{{ $activities->lastItem() }}</strong> 
                            dari <strong>{{ number_format($activities->total()) }}</strong> data
                        </div>
                        <div class="pagination-container m-0">
                            {{ $activities->appends(request()->except('page'))->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-inbox empty-icon d-block"></i>
                <h4 class="fw-bold text-dark">Tidak Ada Aktivitas</h4>
                <p class="text-muted mb-4">Belum ada log aktivitas atau tidak ada data yang cocok dengan filter Anda.</p>
                <a href="{{ route('admin.log') }}" class="btn search-btn d-inline-flex px-4 py-2">
                    <i class="bi bi-arrow-clockwise me-2"></i> Reset Filter
                </a>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi Tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // TomSelect for user search
    if(document.getElementById('user-search')) {
        new TomSelect("#user-search", {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            create: false,
            plugins: ['clear_button'],
            placeholder: 'Pilih / Cari User...',
            maxOptions: 20,
            allowEmptyOption: true,
            load: function(query, callback) {
                if (!query.length) return callback();
                fetch(`/admin/log/user-search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        const results = data.map(item => ({
                            id: item,
                            text: item
                        }));
                        callback(results);
                    })
                    .catch(() => callback());
            },
            onChange: function(value) {
                document.getElementById('userId').value = value;
                document.getElementById('filterForm').submit();
            }
        });
    }

    // Auto-submit status filter
    const statusSelect = document.getElementById('status');
    if(statusSelect){
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // Add loading state to form
    const form = document.getElementById('filterForm');
    const searchBtns = form.querySelectorAll('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        searchBtns.forEach(btn => {
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;
        });
    });
});
</script>
@endpush
@endsection