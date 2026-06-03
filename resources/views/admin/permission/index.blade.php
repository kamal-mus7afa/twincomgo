@extends('layouts.admin')

@section('title', 'Permission User')

@section('content')

@push('styles')
<style>
    /* ===== PREMIUM SAAS DESIGN SYSTEM ===== */
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --primary-light: #e0e7ff;
        
        --success: #10b981;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --info: #0ea5e9;
        
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

    /* ===== BUTTONS ===== */
    .btn-action-main {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-action-main:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        font-size: 14px;
        transition: all 0.2s;
        background: transparent;
    }
    .btn-delete { color: var(--secondary); border: 1px solid transparent; }
    .btn-delete:hover { background: var(--danger-light); color: var(--danger); border-color: var(--danger-light); transform: translateY(-2px); }

    /* ===== TABLE CARD & TOOLBAR ===== */
    .table-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .table-toolbar {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: #fff;
    }

    /* Modern Input Search */
    .modern-search {
        position: relative;
        min-width: 280px;
    }
    .modern-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary);
        z-index: 2;
    }
    .modern-search input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background: var(--bg-light);
        font-size: 14px;
        transition: all 0.2s;
        color: var(--dark);
    }
    .modern-search input:focus {
        background: #fff;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    /* ===== MODERN TABLE ===== */
    .table-container { width: 100%; overflow-x: auto; }
    .table { margin: 0; width: 100%; border-collapse: separate; border-spacing: 0; }
    .table th {
        background: var(--bg-light);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 24px;
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

    /* Setting Item Icon */
    .permission-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .table tbody tr:hover .permission-icon {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary-light);
    }

    .code-badge {
        background: #f1f5f9;
        color: var(--secondary);
        padding: 4px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 12.5px;
        border: 1px solid var(--border-color);
    }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px; }

    /* Animations */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.4s ease backwards; }

    @media (max-width: 768px) {
        .table-toolbar { flex-direction: column; align-items: stretch; }
        .modern-search { width: 100%; min-width: auto; }
        .btn-action-main { width: 100%; justify-content: center; }
    }
</style>
@endpush

<div class="container-fluid py-4 max-w-7xl mx-auto">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card" style="animation-delay: 0.1s">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
            <div class="header-content">
                <h1>
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i> 
                    Hak Akses (Permissions)
                </h1>
                <p class="page-description">Kelola daftar *permission* (izin akses) yang dapat diberikan kepada peran pengguna.</p>
            </div>
            <a href="{{ route('permission.create') }}" class="btn-action-main">
                <i class="bi bi-plus-lg"></i> Tambah Akses
            </a>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.2s">
        
        <!-- Toolbar (Search) -->
        <div class="table-toolbar">
            <div class="modern-search">
                <i class="bi bi-search"></i>
                <input type="text" id="searchPermission" placeholder="Cari nama akses...">
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <table class="table" id="permissionTable">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th>Konfigurasi Hak Akses</th>
                        <th width="150" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="permission-icon">
                                    <i class="bi bi-key-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1">{{ ucwords(str_replace(['_', '-'], ' ', $permission->name)) }}</div>
                                    <div class="code-badge">{{ $permission->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <form id="delete-form-{{ $permission->id }}" action="{{ route('permission.delete', $permission->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')" 
                                        class="action-btn btn-delete" title="Hapus Akses">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-shield-x empty-icon d-block"></i>
                                <h4 class="fw-bold text-dark">Data Kosong</h4>
                                <p class="text-muted mb-4">Sistem belum memiliki konfigurasi hak akses.</p>
                                <a href="{{ route('permission.create') }}" class="btn-action-main d-inline-flex">
                                    <i class="bi bi-plus-lg me-1"></i> Buat Akses Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Fitur Pencarian Cepat (Real-time)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchPermission');
        const table = document.getElementById('permissionTable');
        
        if(searchInput && table) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    // Hindari baris empty state
                    if(row.cells.length === 1 && row.cells[0].colSpan > 1) return;

                    const name = row.cells[1]?.textContent.toLowerCase() || '';
                    
                    if (name.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });

    // Fitur Konfirmasi Hapus SweetAlert
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Akses?',
            html: `Anda yakin ingin menghapus hak akses <b>${name}</b>?<br><small class='text-muted'>Data yang dihapus tidak dapat dikembalikan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // Warna bahaya dari tema
            cancelButtonColor: '#64748b', // Warna abu-abu sekunder dari tema
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'rounded-3',
                cancelButton: 'rounded-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush

@endsection