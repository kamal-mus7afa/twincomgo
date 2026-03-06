@extends('layouts.app')

@section('title', 'Beranda')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 text-primary mb-2">
                        <i class="fas fa-warehouse me-2"></i>Sistem Manajemen Inventori
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>Cari dan kelola barang dengan mudah
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>Cari Barang
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="warehouse" class="form-label">
                                <i class="fas fa-building me-1"></i>Gudang
                            </label>
                            <select id="warehouse" class="form-select" required>
                                <option value="">-- Pilih Gudang --</option>
                                <option value="RESELLER ZAKI">RESELLER ZAKI</option>
                                <option value="RESELLER MARDANI">RESELLER MARDANI</option>
                            </select>
                            <div class="form-text">Pilih gudang untuk melihat stok</div>
                        </div>
                        <div class="col-md-6">
                            <label for="priceCategory" class="form-label">
                                <i class="fas fa-tag me-1"></i>Kategori Harga
                            </label>
                            <select id="priceCategory" class="form-select" required>
                                <option value="">-- Pilih Harga --</option>
                                <option value="RESELLER">Reseller</option>
                                <option value="USER">User</option>
                            </select>
                            <div class="form-text">Pilih kategori harga yang diinginkan</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="search" class="form-label">
                            <i class="fas fa-boxes me-1"></i>Cari Barang
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="search" class="form-control border-start-0" 
                                   placeholder="Masukkan nama atau kode barang...">
                        </div>
                        <div class="form-text text-muted">
                            <i class="fas fa-lightbulb me-1"></i>Mulai ketik minimal 2 karakter untuk mencari
                        </div>
                    </div>
                    
                    <div id="result" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Daftar Barang
                            <span id="item-count" class="badge bg-primary ms-2">{{ count($list) }}</span>
                        </h5>
                        <button onclick="clearList()" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash-alt me-1"></i>Hapus Semua
                        </button>
                        <a href="/list/pdf" target="_blank">Export PDF</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="ps-4">#</th>
                                    <th width="120">
                                        <i class="fas fa-hashtag me-1"></i>Kode
                                    </th>
                                    <th>
                                        <i class="fas fa-cube me-1"></i>Nama Barang
                                    </th>
                                    <th width="120" class="text-center">
                                        <i class="fas fa-boxes me-1"></i>Stok
                                    </th>
                                    <th width="150" class="text-end">
                                        <i class="fas fa-money-bill-wave me-1"></i>Harga
                                    </th>
                                    <th width="80" class="text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="list">
                                @if(count($list) > 0)
                                    @foreach($list as $index => $item)
                                    <tr>
                                        <td class="ps-4 fw-medium text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                {{ $item['code'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $item['name'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $item['stock'] > 10 ? 'success' : ($item['stock'] > 0 ? 'warning' : 'danger') }}">
                                                {{ $item['stock'] }} unit
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <button onclick="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger" 
                                                    title="Hapus item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr id="empty-row">
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                                <h5 class="mb-2">Daftar barang kosong</h5>
                                                <p class="mb-0">Gunakan pencarian di atas untuk menambahkan barang ke daftar</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
    
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom-width: 2px;
        color: #6c757d;
    }
    
    .table tbody tr {
        transition: background-color 0.15s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.85em;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .search-result-item {
        border-radius: 8px;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        cursor: pointer;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
</style>

<script>
document.getElementById('search').addEventListener('input', async e => {
    let q = e.target.value;
    let resultContainer = document.getElementById('result');
    
    // Validasi form
    let warehouse = document.getElementById('warehouse').value;
    let priceCategory = document.getElementById('priceCategory').value;
    
    if (!warehouse || !priceCategory) {
        resultContainer.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Harap lengkapi form!</strong> Pilih gudang dan kategori harga terlebih dahulu.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        resultContainer.style.display = 'block';
        return;
    }
    
    if (q.length < 2) {
        resultContainer.style.display = 'none';
        resultContainer.innerHTML = '';
        return;
    }

    // Tampilkan loading
    resultContainer.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="text-muted">Mencari barang...</span>
        </div>
    `;
    resultContainer.style.display = 'block';

    try {
        let res = await fetch('/list/search?q=' + q);
        let data = await res.json();

        if (data.length === 0) {
            resultContainer.innerHTML = `
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Tidak ditemukan barang untuk pencarian "<strong>${q}</strong>"
                </div>
            `;
        } else {
            let html = data.map(i => `
                <div class="search-result-item p-3 mb-2 border" onclick="addItem(${i.id})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-1">${i.no}</span>
                            <h6 class="mb-1">${i.name}</h6>
                        </div>
                        <i class="fas fa-plus text-success"></i>
                    </div>
                </div>
            `).join('');
            
            resultContainer.innerHTML = html;
        }
    } catch (error) {
        resultContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                Terjadi kesalahan saat mencari barang
            </div>
        `;
    }
});

async function addItem(id) {
    let warehouse = document.getElementById('warehouse').value;
    let priceCategory = document.getElementById('priceCategory').value;

    // Validasi form
    if (!warehouse || !priceCategory) {
        alert('Harap pilih gudang dan kategori harga terlebih dahulu!');
        return;
    }

    try {
        let res = await fetch('/list/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${id}&warehouseName=${encodeURIComponent(warehouse)}&priceCategory=${encodeURIComponent(priceCategory)}`
        });

        let data = await res.json();
        
        // Update tabel
        updateTable(data);
        
        // Clear search results dan input
        document.getElementById('result').style.display = 'none';
        document.getElementById('result').innerHTML = '';
        document.getElementById('search').value = '';
        
        // Tampilkan notifikasi sukses
        showNotification('success', 'Barang berhasil ditambahkan!');
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('danger', 'Terjadi kesalahan saat menambahkan barang');
    }
}

async function removeItem(index) {
    if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        return;
    }

    try {
        let res = await fetch('/list/remove', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `index=${index}`
        });

        let data = await res.json();
        
        // Update tabel
        updateTable(data);
        
        // Tampilkan notifikasi
        showNotification('warning', 'Item berhasil dihapus!');
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('danger', 'Terjadi kesalahan saat menghapus item');
    }
}

// Fungsi untuk update tabel
function updateTable(data) {
    // Update item count
    document.getElementById('item-count').textContent = data.length;

    if (data.length === 0) {
        // Jika data kosong, tampilkan row kosong
        document.getElementById('list').innerHTML = `
            <tr id="empty-row">
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <h5 class="mb-2">Daftar barang kosong</h5>
                        <p class="mb-0">Gunakan pencarian di atas untuk menambahkan barang ke daftar</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        // Generate tabel rows dengan nomor urut
        let html = data.map((i, idx) => `
            <tr>
                <td class="ps-4 fw-medium text-muted">${idx + 1}</td>
                <td>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                        ${i.code}
                    </span>
                </td>
                <td>
                    <div class="fw-medium">${i.name}</div>
                </td>
                <td class="text-center">
                    <span class="badge bg-${i.stock > 10 ? 'success' : (i.stock > 0 ? 'warning' : 'danger')}">
                        ${i.stock} unit
                    </span>
                </td>
                <td class="text-end fw-bold text-primary">
                    Rp ${i.price.toLocaleString('id-ID')}
                </td>
                <td class="text-center pe-4">
                    <button onclick="removeItem(${idx})" class="btn btn-sm btn-outline-danger" 
                            title="Hapus item">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        // Update tabel
        document.getElementById('list').innerHTML = html;
    }
}

async function clearList() {
    if (!confirm('Apakah Anda yakin ingin menghapus semua item dari daftar?')) {
        return;
    }
    
    try {
        await fetch('/list/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Reset tabel ke keadaan kosong
        document.getElementById('list').innerHTML = `
            <tr id="empty-row">
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <h5 class="mb-2">Daftar barang kosong</h5>
                        <p class="mb-0">Gunakan pencarian di atas untuk menambahkan barang ke daftar</p>
                    </div>
                </td>
            </tr>
        `;
        
        // Update item count
        document.getElementById('item-count').textContent = '0';
        
        showNotification('info', 'Semua barang berhasil dihapus dari daftar');
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('danger', 'Terjadi kesalahan saat menghapus daftar');
    }
}

// Fungsi untuk menampilkan notifikasi
function showNotification(type, message) {
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    `;
    
    let icon = type === 'success' ? 'check-circle' : 
               type === 'danger' ? 'exclamation-circle' : 
               type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Tambahkan ke body
    document.body.appendChild(notification);
    
    // Hapus otomatis setelah 3 detik
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Validasi form saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Fokus ke input search
    document.getElementById('search').focus();
    
    // Tambahkan event listener untuk validasi real-time
    document.getElementById('warehouse').addEventListener('change', function() {
        validateForm();
    });
    
    document.getElementById('priceCategory').addEventListener('change', function() {
        validateForm();
    });
    
    // Validasi awal
    validateForm();
});

function validateForm() {
    let warehouse = document.getElementById('warehouse').value;
    let priceCategory = document.getElementById('priceCategory').value;
    let searchInput = document.getElementById('search');
    
    if (warehouse && priceCategory) {
        searchInput.disabled = false;
        searchInput.placeholder = "Masukkan nama atau kode barang...";
        searchInput.classList.remove('bg-light');
    } else {
        searchInput.disabled = true;
        searchInput.placeholder = "Pilih gudang dan kategori harga terlebih dahulu...";
        searchInput.value = "";
        searchInput.classList.add('bg-light');
        document.getElementById('result').style.display = 'none';
        document.getElementById('result').innerHTML = '';
    }
}
</script>

@endsection