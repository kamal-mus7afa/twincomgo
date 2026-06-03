@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@push('styles')
<style>
    /* ===== ENTERPRISE DASHBOARD STYLES ===== */
    .dashboard-header {
        margin-bottom: 32px; display: flex; justify-content: space-between;
        align-items: flex-end; flex-wrap: wrap; gap: 16px;
    }
    .dashboard-title h1 {
        font-size: 24px; font-weight: 700; color: var(--dark, #0f172a);
        margin-bottom: 6px; letter-spacing: -0.5px;
    }
    .dashboard-title p { color: var(--secondary, #64748b); margin: 0; font-size: 15px; }
    
    .date-pill {
        background: #fff; padding: 8px 16px; border-radius: 50px;
        font-size: 14px; font-weight: 500; color: var(--dark, #0f172a);
        border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex; align-items: center; gap: 8px;
    }
    .date-pill i { color: var(--primary, #0d9488); }

    /* --- STAT CARDS --- */
    .stat-card {
        background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
        padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: all 0.3s ease; cursor: pointer; display: flex;
        flex-direction: column; height: 100%; animation: slideUpFade 0.5s ease backwards;
    }
    .stat-card:hover {
        transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .stat-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
    
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px; display: flex;
        align-items: center; justify-content: center; font-size: 20px;
    }
    .icon-primary { background: rgba(13, 148, 136, 0.1); color: #0d9488; }
    .icon-info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
    .icon-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .icon-purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .stat-title { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 28px; font-weight: 700; color: #0f172a; line-height: 1.2; margin-bottom: 8px; }
    
    /* --- TREND BADGES (REAL DATA VISUALIZATION) --- */
    .stat-footer { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #94a3b8; }
    .trend-badge {
        display: inline-flex; align-items: center; padding: 2px 8px; 
        border-radius: 6px; font-weight: 600; font-size: 12px; gap: 2px;
    }
    .trend-up { background: #dcfce7; color: #16a34a; }
    .trend-down { background: #fee2e2; color: #ef4444; }
    .trend-neutral { background: #f1f5f9; color: #64748b; }

    /* --- PANELS (TABLE & ACTIONS) --- */
    .panel-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); animation: slideUpFade 0.5s ease backwards; }
    .panel-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .panel-title { font-size: 16px; font-weight: 600; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px; }
    
    .btn-light-outline {
        background: #fff; border: 1px solid #e2e8f0; color: #64748b;
        font-size: 13px; font-weight: 500; padding: 6px 14px; border-radius: 8px; transition: all 0.2s;
    }
    .btn-light-outline:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

    /* --- CUSTOM TABLE --- */
    .modern-table { margin: 0; width: 100%; }
    .modern-table th { background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; }
    .modern-table td { padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    .modern-table tbody tr:hover { background: #f8fafc; }
    
    .user-avatar-sm {
        width: 36px; height: 36px; border-radius: 50%; background: rgba(13, 148, 136, 0.15);
        color: #0d9488; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
    }

    .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .status-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    /* --- QUICK ACTIONS --- */
    .quick-action-list { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .quick-action-item {
        display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px;
        border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s ease; background: #fff;
    }
    .quick-action-item:hover { border-color: #0d9488; background: #f8fafc; transform: translateX(4px); }
    .qa-icon {
        width: 42px; height: 42px; border-radius: 10px; background: #f1f5f9;
        display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 18px; transition: all 0.2s;
    }
    .quick-action-item:hover .qa-icon { background: rgba(13, 148, 136, 0.1); color: #0d9488; }
    .qa-text h4 { font-size: 14.5px; font-weight: 600; color: #0f172a; margin: 0 0 2px 0; }
    .qa-text p { font-size: 12.5px; color: #64748b; margin: 0; }

    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

<div class="py-2">

    {{-- DASHBOARD HEADER --}}
    <div class="dashboard-header animate-card">
        <div class="dashboard-title">
            <h1>Selamat Datang, {{ Auth::user()->name ?? 'Administrator' }}!</h1>
            <p>Berikut adalah data real-time dari sistem Anda.</p>
        </div>
        <div class="date-pill" id="realtime-clock">
            <i class="bi bi-clock-history"></i>
            <span>{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    {{-- STATISTICS GRID --}}
    <div class="row g-4 mb-4">
        
        {{-- Total Users --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user') }}'" style="animation-delay: 0.1s">
                <div class="stat-card-top">
                    <div class="stat-title">Total Pengguna</div>
                    <div class="stat-icon icon-primary"><i class="bi bi-people-fill"></i></div>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</div>
                    <div class="stat-footer">
                        @php $uGrowth = $userGrowth ?? 0; @endphp
                        @if($uGrowth > 0)
                            <span class="trend-badge trend-up"><i class="bi bi-arrow-up-short"></i>{{ $uGrowth }}%</span>
                        @elseif($uGrowth < 0)
                            <span class="trend-badge trend-down"><i class="bi bi-arrow-down-short"></i>{{ abs($uGrowth) }}%</span>
                        @else
                            <span class="trend-badge trend-neutral">- 0%</span>
                        @endif
                        <span>bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Activity Logs --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.log') }}'" style="animation-delay: 0.2s">
                <div class="stat-card-top">
                    <div class="stat-title">Aktivitas Hari Ini</div>
                    <div class="stat-icon icon-info"><i class="bi bi-activity"></i></div>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($logToday ?? 0, 0, ',', '.') }}</div>
                    <div class="stat-footer">
                        @php $lGrowth = $logGrowth ?? 0; @endphp
                        @if($lGrowth > 0)
                            <span class="trend-badge trend-up"><i class="bi bi-arrow-up-short"></i>{{ $lGrowth }}%</span>
                        @elseif($lGrowth < 0)
                            <span class="trend-badge trend-down"><i class="bi bi-arrow-down-short"></i>{{ abs($lGrowth) }}%</span>
                        @else
                            <span class="trend-badge trend-neutral">- 0%</span>
                        @endif
                        <span>hari kemarin</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Accurate Accounts --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" onclick="window.location='{{ route('aa.index') }}'" style="animation-delay: 0.3s">
                <div class="stat-card-top">
                    <div class="stat-title">Akun Accurate</div>
                    <div class="stat-icon icon-purple"><i class="bi bi-diagram-3-fill"></i></div>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalAccurate ?? 0, 0, ',', '.') }}</div>
                    <div class="stat-footer">
                        @php $aGrowth = $accurateGrowth ?? 0; @endphp
                        @if($aGrowth > 0)
                            <span class="trend-badge trend-up"><i class="bi bi-arrow-up-short"></i>{{ $aGrowth }}%</span>
                        @elseif($aGrowth < 0)
                            <span class="trend-badge trend-down"><i class="bi bi-arrow-down-short"></i>{{ abs($aGrowth) }}%</span>
                        @else
                            <span class="trend-badge trend-neutral">- 0%</span>
                        @endif
                        <span>bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Reseller Accounts --}}
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" onclick="window.location='{{ route('admin.user', ['status' => 'reseller']) }}'" style="animation-delay: 0.4s">
                <div class="stat-card-top">
                    <div class="stat-title">Reseller Aktif</div>
                    <div class="stat-icon icon-warning"><i class="bi bi-shop-window"></i></div>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalReseller ?? 0, 0, ',', '.') }}</div>
                    <div class="stat-footer">
                        @php $rGrowth = $resellerGrowth ?? 0; @endphp
                        @if($rGrowth > 0)
                            <span class="trend-badge trend-up"><i class="bi bi-arrow-up-short"></i>{{ $rGrowth }}%</span>
                        @elseif($rGrowth < 0)
                            <span class="trend-badge trend-down"><i class="bi bi-arrow-down-short"></i>{{ abs($rGrowth) }}%</span>
                        @else
                            <span class="trend-badge trend-neutral">- 0%</span>
                        @endif
                        <span>bulan lalu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT ACTIVITY & QUICK ACTIONS --}}
    <div class="row g-4">
        {{-- RECENT ACTIVITY --}}
        <div class="col-xl-8">
            <div class="panel-card" style="animation-delay: 0.5s">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="bi bi-card-list text-secondary"></i> Log Aktivitas Terbaru
                    </h3>
                    <a href="{{ route('admin.log') }}" class="btn-light-outline text-decoration-none">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                                <th>Status</th>
                                <th class="text-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLogs ?? [] as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="user-avatar-sm">
                                                {{ strtoupper(substr($log->log_name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="fw-semibold text-dark">
                                                {{ $log->log_name ?? 'Unknown User' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-secondary">{{ $log->description }}</span></td>
                                    <td>
                                        @php 
                                            $status = strtolower(optional($log->causer)->status ?? 'completed');
                                            $statusClass = $status === 'success' ? 'status-success' : 
                                                          ($status === 'warning' ? 'status-warning' : 'status-danger');
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td class="text-end text-secondary" style="font-size: 13px;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            Belum ada aktivitas tercatat hari ini.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="col-xl-4">
            <div class="panel-card" style="animation-delay: 0.6s">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="bi bi-lightning-charge text-warning"></i> Akses Cepat
                    </h3>
                </div>
                <div class="quick-action-list">
                    <a href="{{ route('admin.user') }}" class="quick-action-item">
                        <div class="qa-icon"><i class="bi bi-person-plus-fill"></i></div>
                        <div class="qa-text">
                            <h4>Kelola Pengguna</h4>
                            <p>Tambah, edit, atau hapus akses akun.</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.items') }}" class="quick-action-item">
                        <div class="qa-icon"><i class="bi bi-box-seam-fill"></i></div>
                        <div class="qa-text">
                            <h4>Barang & Jasa</h4>
                            <p>Atur katalog produk dan layanan.</p>
                        </div>
                    </a>
                    <a href="{{ route('aa.index') }}" class="quick-action-item">
                        <div class="qa-icon"><i class="bi bi-diagram-3-fill"></i></div>
                        <div class="qa-text">
                            <h4>Accurate Token</h4>
                            <p>Sinkronisasi API & Pengaturan.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time Clock
    function updateClock() {
        const clockEl = document.querySelector('#realtime-clock span');
        if(!clockEl) return;
        
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        clockEl.textContent = now.toLocaleDateString('id-ID', options).replace(/\./g, ':');
    }
    setInterval(updateClock, 1000);
    updateClock();
});
</script>
@endpush

@endsection