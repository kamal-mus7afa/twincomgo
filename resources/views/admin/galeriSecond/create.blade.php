@extends('layouts.admin')

@section('page-title', 'Galeri Second')

@section('content')

<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h5 class="mb-1 fw-bold text-dark">
                <i class="bi bi-image me-2 text-primary"></i>Tambah Barang Second
            </h5>
            <p class="text-muted small mb-0">Upload gambar barang second ke galeri</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                <i class="bi bi-database me-1"></i> Galeri Second
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri - Informasi Barang -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-box-seam text-primary me-2"></i>Informasi Barang
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">Barang</label>
                        <select id="item_select" class="form-select"></select>
                        <div class="form-text text-muted small">Ketik untuk mencari barang</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">Serial Number (SN)</label>
                        <select id="sn_select" class="form-select"></select>
                        <div class="form-text text-muted small">Pilih serial number yang tersedia</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan - Upload Gambar -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-cloud-upload text-primary me-2"></i>Upload Gambar
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center bg-light" id="uploadArea" style="cursor: pointer;">
                        <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-3 d-block"></i>
                        <p class="mb-2 fw-semibold">Seret & lepas gambar di sini</p>
                        <p class="text-muted small mb-3">atau</p>
                        <label class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="bi bi-folder2-open me-2"></i>Pilih File
                            <input type="file" id="gambar" multiple class="d-none">
                        </label>
                        <p class="text-muted small mt-3 mb-0">
                            <i class="bi bi-info-circle"></i> Format: JPG, PNG, JPEG
                        </p>
                    </div>

                    <div id="preview" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Kolom Garansi -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-shield-check text-primary me-2"></i>Informasi Garansi
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="checkChecked" style="width: 3rem; height: 1.5rem;">
                        <label class="form-check-label fw-semibold ms-2">Sisa Garansi</label>
                    </div>

                    <div id="input_garansi" class="bg-light rounded-3 p-4" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-2">Tipe Garansi</label>
                                <select id="type_garansi" class="form-select">
                                    <option value="">Pilih</option>
                                    <option value="resmi">Resmi</option>
                                    <option value="distributor">Distributor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-2">Berlaku s/d</label>
                                <input type="date" id="tanggal_garansi" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-end gap-3 mt-2">
                <button type="button" class="btn btn-light border rounded-pill px-4" onclick="resetForm()">
                    <i class="bi bi-arrow-repeat me-2"></i>Reset
                </button>
                <button id="btnSave" onclick="saveData()" class="btn btn-primary rounded-pill px-5">
                    <i class="bi bi-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Preview Gambar</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="previewImage" src="" alt="Preview" class="img-fluid rounded-3" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed {
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }
    .border-dashed:hover {
        border-color: #0d9488;
        background-color: #f0fdfa !important;
    }
    .upload-area.drag-over {
        border-color: #0d9488;
        background-color: #f0fdfa !important;
    }
    .preview-img-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .preview-img-thumb:hover {
        transform: scale(1.05);
        opacity: 0.9;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .rounded-3 {
        border-radius: 0.75rem !important;
    }
    @media (max-width: 768px) {
        .btn-primary, .btn-light {
            width: 100%;
        }
    }
</style>

<script>
    let selectedFiles = [];
    let itemTom, snTom;
    let selectedItem = null;

    // =======================
    // INIT
    // =======================
    document.addEventListener("DOMContentLoaded", function () {

        document.getElementById('checkChecked').addEventListener('change', function () {
            document.getElementById('input_garansi').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('gambar').addEventListener('change', function(e) {
            let newFiles = Array.from(e.target.files);
            selectedFiles = selectedFiles.concat(newFiles);
            renderPreview();
        });

        snTom = new TomSelect("#sn_select", {
            valueField: "value",
            labelField: "text",
            searchField: ["text"],
            optgroupField: "warehouse",
            options: []
        });

        itemTom = new TomSelect("#item_select", {
            valueField: "no",
            labelField: "text",
            searchField: ["text"],
            load: function(query, callback) {
                if (!query.length) return callback();
                fetch(`/admin/galeri/list?search=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        callback(data.map(item => ({
                            no: item.no,
                            text: item.name + " (" + item.no + ")",
                            id: item.id,
                            name: item.name,
                            category: item.itemCategory ? item.itemCategory.name : '-',
                            category_id: item.itemCategory ? item.itemCategory.id : null
                        })));
                    });
            },
            onChange: function(value) {
                if (value) {
                    selectedItem = this.options[value];
                    loadSN(value);
                }
            }
        });

        // Drag & drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('gambar');
        if (uploadArea) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });
            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                const files = Array.from(e.dataTransfer.files);
                const imageFiles = files.filter(file => file.type.startsWith('image/'));
                if (imageFiles.length > 0) {
                    selectedFiles = selectedFiles.concat(imageFiles);
                    renderPreview();
                    Toastify({ text: `${imageFiles.length} file ditambahkan`, duration: 2000, backgroundColor: "#0d9488" }).showToast();
                } else {
                    Swal.fire('Peringatan', 'Hanya file gambar', 'warning');
                }
            });
        }
    });

    // =======================
    // PREVIEW FILE (DENGAN THUMBNAIL & CLICK PREVIEW)
    // =======================
    function renderPreview() {
        let preview = document.getElementById('preview');
        preview.innerHTML = '';

        if (selectedFiles.length === 0) {
            preview.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-images fs-1 d-block mb-2"></i><small>Belum ada gambar</small></div>';
            return;
        }

        selectedFiles.forEach((file, index) => {
            let div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between bg-white p-2 mb-2 rounded-3 shadow-sm';
            
            // Buat thumbnail
            let thumbnailHtml = '<div class="bg-light rounded-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="bi bi-file-image text-secondary"></i></div>';
            if (file.type.startsWith('image/')) {
                thumbnailHtml = `<img class="preview-img-thumb" id="thumb-${index}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">`;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById(`thumb-${index}`);
                    if (img) img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
            
            div.innerHTML = `
                <div class="d-flex align-items-center gap-3 flex-grow-1" style="cursor: pointer;" onclick="previewImage(${index})">
                    ${thumbnailHtml}
                    <div>
                        <div class="fw-semibold small">${file.name.substring(0, 30)}${file.name.length > 30 ? '...' : ''}</div>
                        <div class="text-muted small">${(file.size / 1024).toFixed(1)} KB</div>
                    </div>
                </div>
                <button type="button" onclick="removeFile(${index})" class="btn btn-sm btn-link text-danger">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            preview.appendChild(div);
        });
    }

    // Fungsi preview gambar
    function previewImage(index) {
        const file = selectedFiles[index];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                document.getElementById('previewImage').src = e.target.result;
                modal.show();
            };
            reader.readAsDataURL(file);
        }
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        renderPreview();
    }

    // Reset form
    function resetForm() {
        Swal.fire({
            title: 'Reset Form?',
            text: 'Semua data akan hilang',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                selectedFiles = [];
                renderPreview();
                document.getElementById('gambar').value = '';
                if (itemTom) itemTom.clear();
                if (snTom) snTom.clear();
                document.getElementById('checkChecked').checked = false;
                document.getElementById('input_garansi').style.display = 'none';
                document.getElementById('type_garansi').value = '';
                document.getElementById('tanggal_garansi').value = '';
                selectedItem = null;
                Swal.fire('Berhasil!', 'Form telah direset', 'success');
            }
        });
    }

    // =======================
    // LOAD SN
    // =======================
    async function loadSN(itemNo) {
        snTom.clear();
        snTom.clearOptions();
        snTom.clearOptionGroups();

        let data = await fetch(`/admin/galeri/sn?itemNo=${itemNo}`).then(res => res.json());
        let usedSN = [];
        try {
            let res = await fetch('/draft/used-sn');
            if (res.ok) usedSN = await res.json();
        } catch (e) {
            console.warn("used-sn gagal");
        }

        let groups = {};
        data.forEach(item => {
            let warehouse = item.warehouse ? item.warehouse.name : '-';
            let sn = item.serialNumber ? item.serialNumber.number : '-';
            let qty = item.quantity ? item.quantity : 0;
            if (qty <= 0) return;
            if (usedSN.includes(sn)) return;
            if (!groups[warehouse]) {
                groups[warehouse] = true;
                snTom.addOptionGroup(warehouse, { value: warehouse, label: warehouse });
            }
            snTom.addOption({ value: sn, text: sn + ' | ' + warehouse + ' | Stock: ' + qty, warehouse: warehouse, qty: qty });
        });
        snTom.refreshOptions(false);
    }

    // =======================
    // BUTTON LOADING
    // =======================
    function setLoading(state) {
        const btn = document.getElementById('btnSave');
        if (state) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = 'Menyimpan...';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalText || 'Simpan';
        }
    }

    // =======================
    // SAVE DATA
    // =======================
    function saveData() {
        let csrf = document.querySelector('meta[name="csrf-token"]');
        let snValue = snTom.getValue();
        let snData = snTom.options[snValue];
        let typeGaransi = document.getElementById('type_garansi').value;
        let tanggalGaransi = document.getElementById('tanggal_garansi').value;

        if (!selectedItem) return alert("Pilih item dulu");
        if (!snValue) return alert("Pilih SN dulu");
        if (selectedFiles.length === 0) return alert("Upload minimal 1 gambar");

        let formData = new FormData();
        formData.append('item_id', selectedItem.id);
        formData.append('item_no', selectedItem.no);
        formData.append('item_name', selectedItem.name);
        formData.append('category', selectedItem.category);
        formData.append('category_id', selectedItem.category_id);
        formData.append('sn', snValue);
        formData.append('warehouse', snData.warehouse);
        formData.append('qty', snData.qty);
        formData.append('type_garansi', typeGaransi);
        formData.append('tanggal_real', tanggalGaransi);
        formData.append('status', 'unkeep');

        selectedFiles.forEach(file => { formData.append('gambar[]', file); });

        setLoading(true);
        fetch('/draft/add', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            setLoading(false);
            if (!res.success) return alert('Gagal');
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil disimpan', timer: 2000, showConfirmButton: false });
            selectedFiles = [];
            renderPreview();
            document.getElementById('gambar').value = '';
            itemTom.clear();
            snTom.clear();
        })
        .catch(err => { setLoading(false); console.error(err); });
    }
</script>

@endsection