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
                        <h4 class="mb-0 fw-semibold">Edit Galeri Second</h4>
                        <p class="text-muted mb-0 small">Perbarui informasi dan gambar produk</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form
                    action="{{ route('second.update', $second->id) }}"
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

                    <!-- Upload Area -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Gambar
                        </label>

                        <div id="dropArea" class="border-2 border-dashed rounded-4 p-5 text-center bg-light cursor-pointer" style="cursor: pointer; transition: all 0.3s ease;">
                            <i class="bi bi-image fs-1 text-primary mb-3 d-block"></i>
                            <h6 class="mb-2 fw-semibold">Drag & Drop Gambar</h6>
                            <p class="text-muted mb-2 small">atau</p>
                            <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('images').click()">
                                <i class="bi bi-folder2-open me-2"></i>Pilih File
                            </button>
                            <input
                                type="file"
                                id="images"
                                name="images[]"
                                multiple
                                accept="image/*"
                                hidden>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Format: JPG, PNG, GIF | Maks: 2MB per gambar
                            </p>
                        </div>
                    </div>

                    <!-- Preview Gambar -->
                    <div id="previewImages" class="d-flex flex-wrap gap-3 mt-4 mb-4">
                        <!-- Preview akan muncul di sini -->
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

    // Fungsi render preview gambar
    function renderPreview() {
        let preview = document.getElementById('previewImages');
        preview.innerHTML = '';
        
        if (dt.files.length === 0) {
            preview.innerHTML = `
                <div class="alert alert-secondary w-100 text-center py-4">
                    <i class="bi bi-image fs-2 d-block mb-2"></i>
                    <span>Belum ada gambar yang dipilih</span>
                </div>
            `;
            return;
        }
        
        Array.from(dt.files).forEach((file, index) => {
            let reader = new FileReader();
            
            reader.onload = function(e) {
                let wrapper = document.createElement('div');
                wrapper.className = 'preview-wrapper';
                wrapper.style.position = 'relative';
                
                let img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';
                
                let remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'remove-btn';
                remove.innerHTML = '×';
                remove.title = 'Hapus gambar';
                
                remove.onclick = function() {
                    Swal.fire({
                        title: 'Hapus gambar?',
                        text: 'Gambar akan dihapus dari daftar upload',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let newDt = new DataTransfer();
                            Array.from(dt.files).forEach((f, i) => {
                                if (i !== index) {
                                    newDt.items.add(f);
                                }
                            });
                            dt = newDt;
                            input.files = dt.files;
                            renderPreview();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Gambar telah dihapus',
                                timer: 1000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    });
                };
                
                wrapper.appendChild(img);
                wrapper.appendChild(remove);
                preview.appendChild(wrapper);
            };
            
            reader.readAsDataURL(file);
        });
    }

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