@extends('layouts.admin')

@section('page-title', 'Pengajuan Harga Jual')

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
        align-items: center;
        justify-content: space-between;
        border-left: 4px solid var(--primary);
    }
    .stat-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .stat-item:nth-child(2) { border-left-color: var(--success); }
    .stat-item:nth-child(3) { border-left-color: var(--danger); }
    .stat-item:nth-child(4) { border-left-color: var(--warning); }

    .stat-content {
        display: flex;
        flex-direction: column;
    }
    .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: var(--dark);
        line-height: 1.2;
    }
    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .icon-primary { background: var(--primary-light); color: var(--primary); }
    .icon-success { background: var(--success-light); color: var(--success); }
    .icon-danger { background: var(--danger-light); color: var(--danger); }
    .icon-warning { background: var(--warning-light); color: var(--warning); }

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
    }
    .modern-search input:focus {
        background: #fff;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    /* Buttons */
    .btn-toolbar {
        display: flex;
        gap: 10px;
    }
    .btn-action-main {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-action-main:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .btn-outline-tool {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-outline-tool:hover {
        background: var(--bg-light);
        color: var(--dark);
        border-color: #cbd5e1;
    }

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

    /* Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 11.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-ready { background: var(--success-light); color: var(--success); }
    .badge-terjual { background: var(--danger-light); color: var(--danger); }
    .badge-diajukan { background: var(--warning-light); color: #d97706; }
    .badge-booked { background: var(--primary-light); color: var(--primary); }
    .badge-other { background: #f1f5f9; color: var(--secondary); }

    .code-badge {
        background: #f1f5f9;
        color: var(--secondary);
        padding: 4px 10px;
        border-radius: 6px;
        font-family: monospace;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid var(--border-color);
    }

    /* Action Buttons in Table */
    .action-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; font-size: 14px; transition: all 0.2s;
    }
    .btn-view { background: var(--info-light); color: var(--info); }
    .btn-view:hover { background: var(--info); color: white; transform: translateY(-2px); }
    .btn-edit { background: var(--primary-light); color: var(--primary); }
    .btn-edit:hover { background: var(--primary); color: white; transform: translateY(-2px); }
    .btn-close-so { background: var(--warning-light); color: #d97706; }
    .btn-close-so:hover { background: #d97706; color: white; transform: translateY(-2px); }
    .btn-delete { background: var(--danger-light); color: var(--danger); }
    .btn-delete:hover { background: var(--danger); color: white; transform: translateY(-2px); }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 16px; }

    /* Animations */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.5s ease backwards; }

    /* Modals Styling Overrides */
    .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; border-radius: 16px 16px 0 0; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
    
    @media (max-width: 768px) {
        .table-toolbar { flex-direction: column; align-items: stretch; }
        .modern-search { width: 100%; min-width: auto; }
        .btn-toolbar { width: 100%; display: grid; grid-template-columns: 1fr 1fr 1fr; }
        .btn-toolbar .btn { justify-content: center; padding: 10px; font-size: 12px; }
    }
</style>
@endpush

<div class="container-fluid py-4">

    <!-- Statistik Section -->
    <div class="stats-grid animate-card" style="animation-delay: 0.1s">
        <div class="stat-item">
            <div class="stat-content">
                <span class="stat-label">Total Item</span>
                <span class="stat-number">{{ number_format($seconds->count()) }}</span>
            </div>
            <div class="stat-icon icon-primary">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-content">
                <span class="stat-label">Ready</span>
                <span class="stat-number">{{ number_format($seconds->where('status', 'ready')->count()) }}</span>
            </div>
            <div class="stat-icon icon-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-content">
                <span class="stat-label">Terjual</span>
                <span class="stat-number">{{ number_format($seconds->where('status', 'terjual')->count()) }}</span>
            </div>
            <div class="stat-icon icon-danger">
                <i class="bi bi-cart-check-fill"></i>
            </div>
        </div>
        
        <div class="stat-item">
            <div class="stat-content">
                <span class="stat-label">Keep / Draft</span>
                <span class="stat-number">{{ number_format($seconds->whereIn('status', ['keep', 'draft'])->count()) }}</span>
            </div>
            <div class="stat-icon icon-warning">
                <i class="bi bi-pencil-square"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Item -->
    <div class="table-card animate-card" style="animation-delay: 0.2s">
        <!-- Toolbar Atas (Search & Buttons) -->
        <div class="table-toolbar">
            <div class="modern-search">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Cari kode, nama, atau S/N...">
            </div>
            
            <div class="btn-toolbar">
                <a href="{{ route('submission') }}" class="btn btn-action-main">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
                <button type="button" class="btn-outline-tool" id="exportBtn">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </button>
                <button type="button" class="btn-outline-tool" id="printBtn">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="table" id="itemTable">
                <thead>
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th width="130">Kode Barang</th>
                        <th>Nama Barang</th>
                        <th width="150">Serial Number</th>
                        <th width="160">Harga Jual</th>
                        <th width="130" class="text-center">Status</th>
                        <th width="150" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seconds as $index => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <span class="code-badge">{{ $item->item_no ?? '-' }}</span>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $item->item_name ?? '-' }}</strong>
                        </td>
                        <td>
                            <span class="code-badge">{{ $item->serial_number ?? '-' }}</span>
                        </td>
                        <td>
                            @if($item->selling_price)
                                <span class="fw-bold text-success fs-6">
                                    Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-muted"><i class="bi bi-dash"></i></span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->status == 'ready')
                                <span class="status-badge badge-ready"><i class="bi bi-check-circle"></i> Ready</span>
                            @elseif($item->status == 'terjual')
                                <span class="status-badge badge-terjual"><i class="bi bi-cart-check"></i> Terjual</span>
                            @elseif($item->status == 'diajukan')
                                <span class="status-badge badge-diajukan"><i class="bi bi-clock"></i> Diajukan</span>
                            @elseif($item->status == 'booked')
                                <span class="status-badge badge-booked"><i class="bi bi-bookmark-check"></i> Booked</span>
                            @else
                                <span class="status-badge badge-other">{{ ucfirst($item->status ?? '-') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('second.editClose', $item->id) }}" class="action-btn btn-close-so" title="Tutup SO">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                                <a href="{{ route('second.edit', $item->id) }}" class="action-btn btn-edit" title="Edit Item">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button" class="action-btn btn-view" onclick="viewDetail({{ $item->id }})" title="Detail Item" data-no-loader>
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                <button type="button" class="action-btn btn-delete" onclick="confirmDelete({{ $item->id }})" title="Hapus Item" data-no-loader>
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-box-seam empty-icon d-block"></i>
                                <h4 class="fw-bold text-dark">Belum Ada Data Item</h4>
                                <p class="text-muted mb-4">Silakan tambahkan produk second baru untuk memulai pengajuan harga.</p>
                                <a href="{{ route('submission') }}" class="btn-action-main d-inline-flex">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Item Sekarang
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

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 mt-3 px-4">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <p class="text-secondary mb-3">Apakah Anda yakin ingin menghapus item ini dari Galeri Second?</p>
                <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                    <div class="d-flex align-items-start gap-2 text-danger">
                        <i class="bi bi-info-circle-fill mt-1"></i>
                        <span class="small fw-medium">Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" style="border-radius: 10px; font-weight: 600;" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" style="border-radius: 10px; font-weight: 600;" id="confirmDeleteBtn">Ya, Hapus Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> Detail Informasi Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4" id="detailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-secondary fw-medium">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" style="border-radius: 10px; font-weight: 600;" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let deleteId = null;

    // Fungsi konfirmasi hapus
    function confirmDelete(id) {
        deleteId = id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Tombol konfirmasi hapus
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!deleteId) return;
        
        Swal.fire({
            title: 'Menghapus...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`/second-products/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan pada server'
            });
        })
        .finally(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
        });
    });

    // Fungsi lihat detail
    function viewDetail(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        const detailContent = document.getElementById('detailContent');
        
        detailContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-secondary fw-medium">Memuat data...</p>
            </div>
        `;
        
        modal.show();
        
        fetch(`/second-products/${id}/show`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                
                let imagesHtml = '';
                if (data.images && data.images.length > 0) {
                    imagesHtml = `
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-bold mb-3">Foto Produk</h6>
                            <div class="row g-2">
                                ${data.images.map(img => `
                                    <div class="col-4 col-md-3">
                                        <img src="${img.url}" class="img-fluid rounded border shadow-sm" style="height: 100px; width: 100%; object-fit: cover;">
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                
                detailContent.innerHTML = `
                    <div class="py-2">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="35%" class="text-secondary fw-medium">Kode Barang</td>
                                    <td width="5%">:</td>
                                    <td><span class="code-badge">${data.item_no || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Nama Barang</td>
                                    <td>:</td>
                                    <td><strong class="text-dark">${data.item_name || '-'}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Serial Number</td>
                                    <td>:</td>
                                    <td><span class="code-badge">${data.serial_number || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Harga Jual</td>
                                    <td>:</td>
                                    <td><span class="fw-bold text-success">
                                        ${data.selling_price ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data.selling_price) : '-'}
                                    </span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Status Item</td>
                                    <td>:</td>
                                    <td>
                                        ${data.status === 'ready' ? '<span class="status-badge badge-ready">Ready</span>' : 
                                          data.status === 'terjual' ? '<span class="status-badge badge-terjual">Terjual</span>' :
                                          '<span class="status-badge badge-diajukan">' + (data.status || '-') + '</span>'}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Customer</td>
                                    <td>:</td>
                                    <td><span class="fw-medium">${data.customer_name || '-'}</span> <span class="text-muted small">(${data.customer_no || '-'})</span></td>
                                </tr>
                                ${data.type_garansi ? `
                                    <tr>
                                        <td class="text-secondary fw-medium">Tipe Garansi</td>
                                        <td>:</td>
                                        <td>${data.type_garansi}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary fw-medium">Batas Garansi</td>
                                        <td>:</td>
                                        <td>${data.tanggal_real || '-'}</td>
                                    </tr>
                                ` : ''}
                                <tr>
                                    <td class="text-secondary fw-medium">Purchase Invoice</td>
                                    <td>:</td>
                                    <td>${data.purchase_invoice_number || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium">Sales Order</td>
                                    <td>:</td>
                                    <td>${data.sales_order_number || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-medium align-middle">Deskripsi Tambahan</td>
                                    <td class="align-middle">:</td>
                                    <td>
                                        <div class="bg-light p-2 rounded text-secondary small">
                                            ${data.description || 'Tidak ada deskripsi.'}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        ${imagesHtml}
                    </div>
                `;
            } else {
                detailContent.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>Gagal memuat detail item.</div>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            detailContent.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>Terjadi kesalahan saat memuat data.</div>
                </div>
            `;
        });
    }

    // Search functionality disesuaikan dengan urutan kolom baru
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('itemTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            
            // Berdasarkan struktur tabel baru:
            // cells[1] = Kode Barang
            // cells[2] = Nama Barang
            // cells[3] = Serial Number
            const itemCode = row.cells[1]?.innerText.toLowerCase() || '';
            const itemName = row.cells[2]?.innerText.toLowerCase() || '';
            const sn = row.cells[3]?.innerText.toLowerCase() || '';
            
            if (itemCode.includes(searchTerm) || itemName.includes(searchTerm) || sn.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });

    // Export to CSV
    document.getElementById('exportBtn').addEventListener('click', function() {
        const table = document.getElementById('itemTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowData = [];
            const cols = row.querySelectorAll('td, th');
            
            // Skip kolom terakhir (Aksi)
            const length = (i === 0) ? cols.length - 1 : cols.length - 1;

            for (let j = 0; j < length; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                text = text.replace(/,/g, ';'); // menghindari konflik pemisah csv
                rowData.push(text);
            }
            csv.push(rowData.join(','));
        }
        
        const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `pengajuan_harga_second_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data berhasil diexport ke CSV',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Print functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        const printContent = document.getElementById('itemTable').cloneNode(true);
        // Hapus kolom aksi pada hasil print
        const headers = printContent.querySelectorAll('th');
        if(headers.length > 0) headers[headers.length-1].remove();
        
        const rows = printContent.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if(cells.length > 0) cells[cells.length-1].remove();
        });

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print - Pengajuan Harga Jual</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 30px; font-family: system-ui, -apple-system, sans-serif; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #dee2e6; padding: 12px; font-size: 14px; text-align: left; }
                        th { background-color: #f8fafc; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h3 class="mb-1">Laporan Pengajuan Harga Jual (Barang Second)</h3>
                    <p class="text-muted mb-4">Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    
                    ${printContent.outerHTML}
                    
                    <script>
                        window.onload = function() { window.print(); window.close(); }
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    });
</script>
@endpush
@endsection