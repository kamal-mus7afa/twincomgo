@extends('layouts.admin')

@section('title', 'Tambah Akses')

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

    /* ===== PANEL CARD ===== */
    .panel-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .panel-header {
        padding: 24px 32px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 16px;
        background: var(--bg-surface);
    }
    .panel-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 4px 0;
    }
    .panel-subtitle {
        font-size: 13.5px;
        color: var(--secondary);
        margin: 0;
    }
    .panel-body {
        padding: 32px;
    }
    .panel-footer {
        padding: 20px 32px;
        background: var(--bg-light);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-group { margin-bottom: 24px; }
    .form-label {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .modern-input-group { position: relative; }
    .modern-input-group i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary);
        font-size: 1.1rem;
        z-index: 2;
    }
    .modern-input {
        width: 100%;
        padding: 12px 16px 12px 46px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-light);
        font-size: 14px;
        color: var(--dark);
        transition: all 0.2s;
    }
    .modern-input:focus {
        background: #fff;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    /* ===== ACTION CHECKBOX CARDS ===== */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 12px;
    }
    
    .action-card {
        display: block;
        cursor: pointer;
        position: relative;
    }
    .action-card input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .action-content {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        background: var(--bg-surface);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .action-card:hover .action-content {
        border-color: #cbd5e1;
        background: var(--bg-light);
    }
    
    /* Active State */
    .action-card input[type="checkbox"]:checked + .action-content {
        border-color: var(--primary);
        background: var(--primary-light);
    }
    
    .action-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--bg-light);
        color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s;
    }
    .action-card input[type="checkbox"]:checked + .action-content .action-icon-box {
        background: var(--primary);
        color: white;
    }

    .action-text {
        flex-grow: 1;
    }
    .action-title {
        display: block;
        font-weight: 700;
        color: var(--dark);
        font-size: 14px;
        margin-bottom: 2px;
    }
    .action-card input[type="checkbox"]:checked + .action-content .action-title {
        color: var(--primary-hover);
    }
    
    .check-indicator {
        color: transparent;
        font-size: 20px;
        transition: all 0.2s;
    }
    .action-card input[type="checkbox"]:checked + .action-content .check-indicator {
        color: var(--primary);
    }

    /* ===== BUTTONS ===== */
    .btn-action-main {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-action-main:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
    }
    .btn-outline-tool {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-outline-tool:hover {
        background: var(--bg-light);
        color: var(--dark);
        border-color: #cbd5e1;
    }

    /* Animations */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.4s ease backwards; }
    
    @media (max-width: 768px) {
        .panel-footer { flex-direction: column; }
        .panel-footer .btn-action-main, .panel-footer .btn-outline-tool { width: 100%; justify-content: center; }
        .action-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

<div class="container-fluid py-4 max-w-7xl mx-auto">

    <div class="row justify-content-center">
        <div class="">
            
            {{-- HEADER SECTION --}}
            <div class="page-header animate-card" style="animation-delay: 0.1s">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
                    <div class="header-content">
                        <h1>
                            <i class="bi bi-shield-plus text-primary me-2"></i> 
                            Tambah Hak Akses
                        </h1>
                        <p class="page-description">Tentukan modul aplikasi dan cakupan aksi yang diizinkan untuk dikelola.</p>
                    </div>
                    <a href="{{ route('permission.index') }}" class="btn-outline-tool">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            {{-- ERROR ALERT --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show animate-card border-0 rounded-4 shadow-sm mb-4" style="animation-delay: 0.15s" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">Gagal menyimpan akses:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            {{-- FORM CARD --}}
            <div class="panel-card animate-card" style="animation-delay: 0.2s">
                <div class="panel-header">
                    <div class="panel-icon">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <div>
                        <h2 class="panel-title">Konfigurasi Modul & Aksi</h2>
                        <p class="panel-subtitle">Isi nama modul dan centang aksi yang ingin didaftarkan ke sistem.</p>
                    </div>
                </div>

                <form action="{{ route('permission.store') }}" method="POST" id="formPermission">
                    @csrf
                    <div class="panel-body">
                        
                        <div class="form-group mb-5">
                            <label class="form-label" for="module">
                                Nama Modul Akses <span class="text-danger ms-1">*</span>
                            </label>
                            <div class="modern-input-group">
                                <i class="bi bi-box"></i>
                                <input type="text" id="module" name="module" class="modern-input" 
                                       value="{{ old('module') }}" placeholder="Contoh: log-activity, transaksi, laporan" required autofocus>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i> Gunakan huruf kecil tanpa spasi (gunakan tanda hubung "-" jika lebih dari satu kata).
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label mb-3">
                                Pilih Aksi yang Diizinkan <span class="text-danger ms-1">*</span>
                            </label>
                            
                            <div class="action-grid">
                                
                                <label class="action-card">
                                    <input type="checkbox" name="actions[]" value="aktif">
                                    <div class="action-content">
                                        <div class="action-icon-box"><i class="bi bi-power"></i></div>
                                        <div class="action-text">
                                            <span class="action-title">Aktif (Active)</span>
                                        </div>
                                        <div class="check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                    </div>
                                </label>

                                <label class="action-card">
                                    <input type="checkbox" name="actions[]" value="view">
                                    <div class="action-content">
                                        <div class="action-icon-box"><i class="bi bi-eye"></i></div>
                                        <div class="action-text">
                                            <span class="action-title">Lihat (View)</span>
                                        </div>
                                        <div class="check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                    </div>
                                </label>

                                <label class="action-card">
                                    <input type="checkbox" name="actions[]" value="create">
                                    <div class="action-content">
                                        <div class="action-icon-box"><i class="bi bi-plus-circle"></i></div>
                                        <div class="action-text">
                                            <span class="action-title">Tambah (Create)</span>
                                        </div>
                                        <div class="check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                    </div>
                                </label>

                                <label class="action-card">
                                    <input type="checkbox" name="actions[]" value="edit">
                                    <div class="action-content">
                                        <div class="action-icon-box"><i class="bi bi-pencil-square"></i></div>
                                        <div class="action-text">
                                            <span class="action-title">Ubah (Edit)</span>
                                        </div>
                                        <div class="check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                    </div>
                                </label>

                                <label class="action-card">
                                    <input type="checkbox" name="actions[]" value="delete">
                                    <div class="action-content">
                                        <div class="action-icon-box"><i class="bi bi-trash3"></i></div>
                                        <div class="action-text">
                                            <span class="action-title">Hapus (Delete)</span>
                                        </div>
                                        <div class="check-indicator"><i class="bi bi-check-circle-fill"></i></div>
                                    </div>
                                </label>

                            </div>
                        </div>

                    </div>
                    
                    <div class="panel-footer">
                        <a href="{{ route('permission.index') }}" class="btn-outline-tool">
                            Batal
                        </a>
                        <button type="submit" class="btn-action-main" id="btnSubmit">
                            <i class="bi bi-save"></i> Simpan Akses
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formPermission');
        const btnSubmit = document.getElementById('btnSubmit');

        if(form) {
            form.addEventListener('submit', function() {
                // Beri indikator loading pada tombol simpan
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
                btnSubmit.disabled = true;
            });
        }
    });
</script>
@endpush

@endsection