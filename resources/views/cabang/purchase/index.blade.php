@extends('layouts.admin')

@section('page-title', 'Galeri Second - Manajemen Item')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-box-seam fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">List Produk Second/2nd</h3>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Kelola semua produk second untuk diajukan harga
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('submission') }}" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-plus-circle me-2"></i>
                        Tambah
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistik Section -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Item</h6>
                                <h3 class="text-white mb-0">{{ $seconds->count() }}</h3>
                            </div>
                            <i class="bi bi-box-seam fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Ready</h6>
                                <h3 class="text-white mb-0">
                                    {{ $seconds->where('status', 'ready')->count() }}
                                </h3>
                            </div>
                            <i class="bi bi-check-circle fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm bg-gradient-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Terjual</h6>
                                <h3 class="text-white mb-0">
                                    {{ $seconds->where('status', 'terjual')->count() }}
                                </h3>
                            </div>
                            <i class="bi bi-cart-check fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Keep/Draft</h6>
                                <h3 class="text-white mb-0">
                                    {{ $seconds->whereIn('status', ['keep', 'draft'])->count() }}
                                </h3>
                            </div>
                            <i class="bi bi-pencil-square fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Item -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   id="searchInput" 
                                   class="form-control border-start-0" 
                                   placeholder="Cari item...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" id="exportBtn">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="printBtn">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="itemTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="140">Serial Number</th>
                            <th>Nama Barang</th>
                            <th width="120">Kode Item</th>
                            <th width="180">Harga</th>
                            <th width="120">Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($seconds as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <code class="bg-light p-1 rounded" style="font-size: 16px;">{{ $item->serial_number ?? '-' }}</code>
                            </td>
                            <td>
                                <strong>{{ $item->item_name ?? '-' }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->item_no ?? '-' }}</span>
                            </td>
                            <td>
                                @if($item->selling_price)
                                    <span class="fw-semibold text-success">
                                        Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'ready')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Ready
                                    </span>
                                @elseif($item->status == 'terjual')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-cart-check me-1"></i> Terjual
                                    </span>
                                @elseif($item->status == 'diajukan')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock me-1"></i> Submission
                                    </span>
                                @elseif($item->status == 'booked')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-box-seam"></i> Booking
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ $item->status ?? '-' }}</span>
                                @endif
                             </td>
                            <td class="text-center">
                                <div class="d-flex gap-2" role="group">
                                    <a href="{{ route('second.editClose', $item->id) }}" 
                                        class="btn btn-sm btn-outline-warning"
                                        title="Tutup SO">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                    <a href="{{ route('second.edit', $item->id) }}" 
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Edit Item">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $item->id }})"
                                            title="Hapus Item" data-no-loader>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            onclick="viewDetail({{ $item->id }})"
                                            title="Detail Item" data-no-loader>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <h5>Belum Ada Data</h5>
                                    <p class="mb-3">Silakan tambahkan item baru ke Galeri Second</p>
                                    <a href="{{ route('submission') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Tambah Item Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- @if($seconds->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Menampilkan {{ $seconds->firstItem() ?? 0 }} - {{ $seconds->lastItem() ?? 0 }} 
                        dari {{ $seconds->total() }} item
                    </div>
                    {{ $seconds->links() }}
                </div>
            </div>
            @endif --}}
        </div>
    </div>
</div>

@endsection
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus item ini?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    Tindakan ini tidak dapat dibatalkan dan akan menghapus data secara permanen.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Detail Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-backdrop {
    z-index: 1040 !important;
}

#deleteModal {
    z-index: 1055 !important;
}

#detailModal {
    z-index: 1060 !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 148, 136, 0.05);
    cursor: pointer;
}

.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}
</style>

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
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat data...</p>
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
                        <div class="mb-3">
                            <label class="fw-semibold">Gambar Produk</label>
                            <div class="row mt-2">
                                ${data.images.map(img => `
                                    <div class="col-4 mb-2">
                                        <img src="${img.url}" class="img-fluid rounded shadow-sm" style="height: 100px; object-fit: cover;">
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                
                detailContent.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="180">Serial Number</th>
                                <td width="10">:</td>
                                <td><code>${data.serial_number || '-'}</code></td>
                            </tr>
                            <tr>
                                <th>Nama Barang</th>
                                <td>:</td>
                                <td><strong>${data.item_name || '-'}</strong></td>
                            </tr>
                            <tr>
                                <th>Kode Item</th>
                                <td>:</td>
                                <td>${data.item_no || '-'}</td>
                            </tr>
                            <tr>
                                <th>Harga Jual</th>
                                <td>:</td>
                                <td class="text-success fw-bold">
                                    ${data.selling_price ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data.selling_price) : '-'}
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>:</td>
                                <td>
                                    ${data.status === 'ready' ? '<span class="badge bg-success">Ready</span>' : 
                                      data.status === 'terjual' ? '<span class="badge bg-danger">Terjual</span>' :
                                      '<span class="badge bg-warning">Keep</span>'}
                                </td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>:</td>
                                <td>${data.customer_name || '-'} (${data.customer_no || '-'})</td>
                            </tr>
                            ${data.type_garansi ? `
                                <tr>
                                    <th>Tipe Garansi</th>
                                    <td>:</td>
                                    <td>${data.type_garansi}</td>
                                </tr>
                                <tr>
                                    <th>Garansi Berlaku</th>
                                    <td>:</td>
                                    <td>${data.tanggal_real || '-'}</td>
                                </tr>
                            ` : ''}
                            <tr>
                                <th>Purchase Invoice</th>
                                <td>:</td>
                                <td>${data.purchase_invoice_number || '-'}</td>
                            </tr>
                            <tr>
                                <th>Sales Order</th>
                                <td>:</td>
                                <td>${data.sales_order_number || '-'}</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>:</td>
                                <td>${data.description || '-'}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td>:</td>
                                <td>${data.created_at || '-'}</td>
                            </tr>
                        </table>
                        ${imagesHtml}
                    </div>
                `;
            } else {
                detailContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat detail item
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error(err);
            detailContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat data
                </div>
            `;
        });
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById('itemTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const sn = row.cells[1]?.innerText.toLowerCase() || '';
            const itemName = row.cells[2]?.innerText.toLowerCase() || '';
            const itemCode = row.cells[3]?.innerText.toLowerCase() || '';
            
            if (sn.includes(searchTerm) || itemName.includes(searchTerm) || itemCode.includes(searchTerm)) {
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
            
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.replace(/,/g, ';');
                rowData.push(text);
            }
            csv.push(rowData.join(','));
        }
        
        const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `galeri_second_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data berhasil diexport',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Print functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        const printContent = document.getElementById('itemTable').cloneNode(true);
        const originalTitle = document.title;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>${originalTitle}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
                    <style>
                        body { padding: 20px; }
                        @media print {
                            .btn-group { display: none; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h3 class="mb-3">Daftar Item Galeri Second</h3>
                        <div class="table-responsive">
                            ${printContent.outerHTML}
                        </div>
                        <div class="mt-3 text-muted text-center">
                            <small>Dicetak pada: ${new Date().toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    });
</script>
@endpush