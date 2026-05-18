@extends('layouts.admin')

@section('page-title', 'Edit Galeri Second')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                        <i class="bi bi-image fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-semibold">Penyesuaian Harga</h4>
                        <p class="text-muted mb-0 small">Perbarui harga produk yang diajukan</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="bg-light rounded-4 p-3 mb-4 border">
                    <table class="table table-borderless mb-0 bg-transparent">
                        <tr>
                            <th class="text-secondary fw-semibold" style="width: 180px">
                                Nama Barang
                            </th>
                            <td class="fw-medium">
                                {{ $second->item_name }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-secondary fw-semibold">
                                Serial Number
                            </th>
                            <td>
                                <span class="font-monospace fw-semibold">
                                    {{ $second->serial_number }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <form
                    action="{{ route('second.close', $second->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="editForm">

                    @csrf
                    @method('PUT')

                    <!-- Harga Jual -->
                    <div class="mb-4">
                        <label for="selling_price" class="form-label fw-semibold mb-2">
                            <i class="bi bi-tag me-2"></i>Harga Jual
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-currency-dollar"></i> Rp
                            </span>
                            <input
                                type="number"
                                name="selling_price"
                                id="selling_price"
                                class="form-control form-control-lg border-start-0"
                                value="{{ $second->selling_price }}"
                                placeholder="Masukkan harga"
                                required>
                        </div>
                        <small class="text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            Masukkan harga dalam Rupiah (tanpa tanda koma)
                        </small>
                    </div>
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('second.index') }}" class="btn btn-outline-secondary px-4" onclick="showLoader()">
                            <i class="bi bi-x-lg me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4" onclick="showLoader()">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table td,
.table th {
    background: transparent !important;
}
.border-dashed {
    border-style: dashed !important;
}
.cursor-pointer {
    cursor: pointer;
}
#dropArea.drag-over {
    background-color: #e7f1ff !important;
    border-color: #0d9488 !important;
}
.preview-wrapper {
    position: relative;
    transition: transform 0.2s ease;
}
.preview-wrapper:hover {
    transform: scale(1.05);
}
.preview-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.remove-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: 2px solid white;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    padding: 0;
    line-height: 1;
}
.remove-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}
</style>

@push('scripts')
<script>
    let dt = new DataTransfer();
    const input = document.getElementById('images');
    const dropArea = document.getElementById('dropArea');

    // Click handler untuk area drop
    dropArea.addEventListener('click', (e) => {
        // Jangan trigger jika yang diklik adalah tombol
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            return;
        }
        input.click();
    });

    // Drag & Drop handlers
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('drag-over');
    });

    dropArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropArea.classList.remove('drag-over');
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('drag-over');
        
        const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
        
        files.forEach(file => {
            dt.items.add(file);
        });
        
        input.files = dt.files;
        renderPreview();
        
        // SweetAlert notifikasi
        if (files.length > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: `${files.length} gambar ditambahkan`,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    });

    // Input change handler
    input.addEventListener('change', function(e) {
        const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
        
        files.forEach(file => {
            dt.items.add(file);
        });
        
        input.files = dt.files;
        renderPreview();
        
        if (files.length > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: `${files.length} gambar ditambahkan`,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    });

    // Validasi form sebelum submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const price = document.getElementById('selling_price').value;
        
        if (!price || price <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Harga jual harus diisi dengan angka yang valid!',
                confirmButtonColor: '#0d9488'
            });
            return false;
        }
        
        // Tampilkan loading toast
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
</script>
@endpush
@endsection