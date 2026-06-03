@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Daftar Customer')

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
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
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
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
    }

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
        min-width: 300px;
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

    /* Customer Avatar */
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        flex-shrink: 0;
    }

    .code-badge {
        background: var(--bg-light);
        color: var(--secondary);
        padding: 6px 12px;
        border-radius: 8px;
        font-family: monospace;
        font-weight: 600;
        font-size: 13px;
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
    .animate-card { animation: slideUpFade 0.5s ease backwards; }

    @media (max-width: 768px) {
        .table-toolbar { flex-direction: column; align-items: stretch; }
        .modern-search { width: 100%; min-width: auto; }
        .btn-action-main { width: 100%; justify-content: center; }
    }
</style>
@endpush

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card" style="animation-delay: 0.1s">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
            <div class="header-content">
                <h1>
                    <i class="bi bi-people-fill text-primary me-2"></i> 
                    Daftar Customer
                </h1>
                <p class="page-description">Kelola dan pantau seluruh data kontak pelanggan Anda di sistem.</p>
            </div>
            <a href="{{ route('customer.create') }}" class="btn-action-main">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.2s">
        
        <div class="table-toolbar">
            <div class="modern-search">
                <i class="bi bi-search"></i>
                <input type="text" id="searchCustomer" placeholder="Cari nama, nomor, atau HP customer...">
            </div>
        </div>

        <div class="table-container">
            <table class="table" id="customerTable">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th>Informasi Customer</th>
                        <th width="250">Nomor Customer</th>
                        <th width="250">No. Handphone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-avatar">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $customer->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="code-badge">
                                <i class="bi bi-hash text-muted"></i> {{ $customer->customer_number ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-telephone-fill" style="font-size: 12px;"></i>
                                </div>
                                <span class="fw-medium">{{ $customer->phone ?? 'Tidak ada nomor' }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-person-vcard empty-icon d-block"></i>
                                <h4 class="fw-bold text-dark">Belum Ada Data Customer</h4>
                                <p class="text-muted mb-4">Silakan tambahkan data pelanggan baru untuk mulai mengelola.</p>
                                <a href="{{ route('customer.create') }}" class="btn-action-main d-inline-flex">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Customer Sekarang
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
    document.addEventListener('DOMContentLoaded', function() {
        // Fitur Pencarian Real-time pada Tabel
        const searchInput = document.getElementById('searchCustomer');
        const table = document.getElementById('customerTable');
        
        if(searchInput && table) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    // Cek apakah baris ini adalah baris "Empty State"
                    if(row.cells.length === 1 && row.cells[0].colSpan > 1) return;

                    const name = row.cells[1]?.textContent.toLowerCase() || '';
                    const number = row.cells[2]?.textContent.toLowerCase() || '';
                    const phone = row.cells[3]?.textContent.toLowerCase() || '';
                    
                    if (name.includes(searchTerm) || number.includes(searchTerm) || phone.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection