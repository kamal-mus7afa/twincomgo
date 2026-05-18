@extends('layouts.app')

@section('page-title', 'Pengajuan Harga')

@section('content')

<div class="container-fluid">
    <div class="card p-4 text-white mb-3" style="background: rgba(0, 0, 0, 0.7);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Daftar Pengajuan</h3>
                <p>Kelola pengajuan harga barang second/2nd</p>
            </div>
            <div>
                <a href="{{route('submission')}}" class="btn btn-outline-primary">
                    + Tambah
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive rounded-3">
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
                                <i class="bi bi-clock me-1"></i> Diajukan
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
                        <button type="button" 
                                class="btn btn-sm btn-outline-info" 
                                onclick="viewDetail({{ $item->id }})"
                                title="Detail Item" data-no-loader>
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('second.edit', $item->id) }}" 
                            class="btn btn-sm btn-outline-secondary"
                            title="Edit Item">
                            <i class="bi bi-pencil-square"></i>
                        </a>
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

@push('scripts')
<script>
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
                                      '<span class="badge bg-warning">Diajukan</span>'}
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
</script>
@endpush

@endsection