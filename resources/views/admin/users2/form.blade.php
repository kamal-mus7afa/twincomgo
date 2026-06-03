@extends('layouts.admin')

@section('page-title', $user->exists ? 'Edit User' : 'Create User')

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
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --info: #0ea5e9;
        --purple: #8b5cf6;
        
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

    /* ===== PANEL CARD & FORM HEADER ===== */
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

    /* ===== FORM ELEMENTS ===== */
    .form-body {
        padding: 32px;
    }
    .form-section-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    
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
    .required::after { content: "*"; color: var(--danger); margin-left: 2px; }

    /* Input Modern with Icon */
    .modern-input-group {
        position: relative;
    }
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
    
    .form-text {
        font-size: 12.5px;
        color: var(--secondary);
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ===== ROLE / STATUS SELECTOR ===== */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
        margin-top: 10px;
    }
    .status-option input { display: none; }
    .status-card {
        border: 1.5px solid var(--border-color);
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bg-surface);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .status-card:hover {
        border-color: #cbd5e1;
        background: var(--bg-light);
    }
    .status-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: #f1f5f9;
        color: var(--secondary);
        transition: all 0.2s ease;
    }
    .status-name {
        font-weight: 700;
        font-size: 14px;
        color: var(--dark);
    }
    
    /* Checked States */
    .status-option input:checked + .status-card {
        border-color: var(--primary);
        background: rgba(79, 70, 229, 0.04);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
    }
    .status-option input[value="admin"]:checked + .status-card .status-icon-box { background: rgba(139,92,246,0.15); color: var(--purple); }
    .status-option input[value="KARYAWAN"]:checked + .status-card .status-icon-box { background: var(--info-light); color: var(--info); }
    .status-option input[value="RESELLER"]:checked + .status-card .status-icon-box { background: var(--warning-light); color: #d97706; }
    .status-option input[value="USER"]:checked + .status-card .status-icon-box { background: var(--success-light); color: var(--success); }

    /* ===== BUTTONS ===== */
    .form-actions {
        padding: 24px 32px;
        background: var(--bg-light);
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .btn-submit {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
    }
    .btn-cancel {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-cancel:hover {
        background: #f1f5f9;
        color: var(--dark);
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.5s ease backwards; }

    @media (max-width: 768px) {
        .form-actions { flex-direction: column; }
        .form-actions .btn-submit, .form-actions .btn-cancel { width: 100%; justify-content: center; }
        .form-body { padding: 20px; }
        .panel-header { padding: 20px; }
    }
</style>
@endpush

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
            <div class="header-content">
                <h1>
                    <i class="bi bi-person-{{ $user->exists ? 'gear' : 'plus' }} text-primary me-2"></i>
                    {{ $user->exists ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}
                </h1>
                <p class="page-description">
                    {{ $user->exists ? 'Perbarui informasi dan hak akses pengguna ini.' : 'Daftarkan pengguna baru untuk memberikan akses ke dalam sistem.' }}
                </p>
            </div>
            <a href="{{ route('admin.user') }}" class="btn-back-pill">
                <i class="bi bi-arrow-left"></i> <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show animate-card border-0 rounded-4 shadow-sm" style="animation-delay: 0.1s" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1">Mohon perbaiki kesalahan berikut:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
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
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div>
                <h2 class="panel-title">Formulir Pengguna</h2>
                <p class="panel-subtitle">Lengkapi form di bawah ini dengan data yang valid</p>
            </div>
        </div>

        <form method="post" action="{{ $user->exists ? route('users2.update', $user->id) : route('users2.store') }}" id="userForm">
            @csrf
            @if($user->exists) @method('PUT') @endif

            <div class="form-body">
                {{-- BASIC INFORMATION SECTION --}}
                <div class="form-section-title">
                    <i class="bi bi-info-circle text-primary"></i> Informasi Dasar
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required">Nama Lengkap</label>
                            <div class="modern-input-group">
                                <i class="bi bi-person"></i>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                       class="modern-input" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required">Alamat Email</label>
                            <div class="modern-input-group">
                                <i class="bi bi-envelope"></i>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                       class="modern-input" placeholder="email@contoh.com" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PASSWORD SECTION --}}
                <div class="form-section-title mt-2">
                    <i class="bi bi-shield-lock text-primary"></i> Kemanan Akun
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label {{ !$user->exists ? 'required' : '' }}">Kata Sandi</label>
                            <div class="modern-input-group">
                                <i class="bi bi-key"></i>
                                <input type="password" name="password" class="modern-input" 
                                       placeholder="{{ $user->exists ? 'Kosongkan jika tidak ingin diubah' : 'Masukkan kata sandi' }}"
                                       {{ !$user->exists ? 'required' : '' }}>
                            </div>
                            @if($user->exists)
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    Kosongkan field ini jika tidak ingin mengubah password lama.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label {{ !$user->exists ? 'required' : '' }}">Konfirmasi Kata Sandi</label>
                            <div class="modern-input-group">
                                <i class="bi bi-key-fill"></i>
                                <input type="password" name="password_confirmation" class="modern-input" 
                                       placeholder="Ulangi kata sandi"
                                       {{ !$user->exists ? 'required' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROLE & PERMISSIONS SECTION --}}
                <div class="form-section-title mt-2">
                    <i class="bi bi-person-badge text-primary"></i> Hak Akses & Status
                </div>

                <div class="form-group mb-0">
                    <label class="form-label required">Pilih Peran (Role) Pengguna</label>
                    
                    <div class="status-grid">
                        <label class="status-option">
                            <input type="radio" name="status" value="admin" {{ old('status', $user->status) == 'admin' ? 'checked' : '' }} required>
                            <div class="status-card">
                                <div class="status-icon-box"><i class="bi bi-shield-check"></i></div>
                                <span class="status-name">Administrator</span>
                            </div>
                        </label>

                        <label class="status-option">
                            <input type="radio" name="status" value="KARYAWAN" {{ old('status', $user->status) == 'KARYAWAN' ? 'checked' : '' }} required>
                            <div class="status-card">
                                <div class="status-icon-box"><i class="bi bi-person-vcard"></i></div>
                                <span class="status-name">Karyawan</span>
                            </div>
                        </label>

                        <label class="status-option">
                            <input type="radio" name="status" value="RESELLER" {{ old('status', $user->status) == 'RESELLER' ? 'checked' : '' }} required>
                            <div class="status-card">
                                <div class="status-icon-box"><i class="bi bi-shop"></i></div>
                                <span class="status-name">Reseller</span>
                            </div>
                        </label>

                        <label class="status-option">
                            <input type="radio" name="status" value="USER" {{ old('status', $user->status) == 'USER' ? 'checked' : '' }} required>
                            <div class="status-card">
                                <div class="status-icon-box"><i class="bi bi-person"></i></div>
                                <span class="status-name">User Biasa</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-{{ $user->exists ? 'save' : 'check2-circle' }}"></i>
                    {{ $user->exists ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                </button>
                <a href="{{ route('admin.user') }}" class="btn-cancel">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    const submitBtn = form.querySelector('.btn-submit');
    
    // Loading state pada tombol submit
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
        submitBtn.disabled = true;
    });

    // Password strength indicator (Sederhana)
    const passwordInput = form.querySelector('input[name="password"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = document.getElementById('password-strength') || createPasswordStrength();
            
            if (password.length === 0) {
                strength.style.display = 'none';
                return;
            }
            
            strength.style.display = 'flex';
            let strengthText = '';
            let strengthColor = '';
            
            if (password.length < 6) {
                strengthText = 'Terlalu Pendek (Lemah)';
                strengthColor = 'var(--danger)';
            } else if (password.length < 10) {
                strengthText = 'Sedang';
                strengthColor = 'var(--warning)';
            } else {
                strengthText = 'Kuat';
                strengthColor = 'var(--success)';
            }
            
            strength.innerHTML = `<i class="bi bi-shield-lock-fill"></i> Kekuatan Sandi: <strong>${strengthText}</strong>`;
            strength.style.color = strengthColor;
        });
    }

    function createPasswordStrength() {
        const strength = document.createElement('div');
        strength.id = 'password-strength';
        strength.className = 'form-text mt-2';
        passwordInput.parentNode.appendChild(strength);
        return strength;
    }
});
</script>
@endpush
@endsection