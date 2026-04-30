@extends('layouts.admin')

@section('page-title', 'Galeri Second')

@section('content')

<div class="card-header bg-transparent border-0 py-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                <i class="bi bi-shop fs-3"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-semibold">Daftar Galeri Second</h4>
                <p class="text-muted mb-0 small">Kelola data produk galeri second</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('second.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">

        <!-- Step 1: Cari Invoice -->
        <div id="stepInvoice">
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-receipt me-2"></i>Nomor Purchase Invoice
                </label>
                <div class="input-group">
                    <input type="text"
                        id="numberInvoice"
                        class="form-control form-control-lg"
                        placeholder="Masukkan nomor invoice">
                    <button type="button"
                        class="btn btn-primary px-4"
                        onclick="getInvoice()">
                        <i class="bi bi-search me-2"></i>Cari Invoice
                    </button>
                </div>
                <small class="text-muted">Masukkan nomor invoice untuk memulai</small>
            </div>
        </div>

        <!-- Step 2: Detail Invoice & Form (Hidden dulu) -->
        <div id="stepForm" style="display: none;">
            
            <!-- Detail Invoice -->
            <div id="invoiceResult"></div>

            <!-- Form Input Lainnya -->
            <div class="mt-4 pt-3 border-top">
                <h5 class="mb-3 fw-semibold">
                    <i class="bi bi-pencil-square me-2"></i>Informasi Tambahan
                </h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Cabang</label>
                    <select id="branchName" class="form-control">
                        <option value="">Pilih Cabang</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Gudang</label>
                    <select id="warehouseName" class="form-control">
                        <option value="">Pilih Gudang</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer</label>
                    <select id="customerName" class="form-control">
                        <option value="">Pilih Customer</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="form-control"
                        placeholder="Masukkan keterangan tambahan..."></textarea>
                </div>

                <!-- Sisa Garansi -->
                <div class="mb-3">
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="hasWarranty" 
                            name="has_warranty"
                            value="1">
                        <label class="form-check-label fw-semibold" for="hasWarranty">
                            <i class="bi bi-shield-check me-2"></i>Sisa Garansi
                        </label>
                    </div>
                    <small class="text-muted ms-4">Centang jika produk masih memiliki sisa garansi</small>
                </div>

                <!-- Detail Garansi (Hidden by default) -->
                <div id="warrantyDetails" style="display: none;" class="mb-3 ps-4 border-start border-3 border-primary">
                    <h6 class="mb-3 fw-semibold text-primary">
                        <i class="bi bi-info-circle me-2"></i>Detail Garansi
                    </h6>
                    
                    <div class="mb-3">
                        <label for="type_garansi" class="form-label fw-semibold">
                            Tipe Garansi
                        </label>
                        <select name="type_garansi" id="type_garansi" class="form-select">
                            <option value="">Pilih Tipe Garansi</option>
                            <option value="resmi">Resmi</option>
                            <option value="distributor">Distributor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_real" class="form-label fw-semibold">
                            Berlaku Sampai
                        </label>
                        <input 
                            type="date" 
                            name="tanggal_real" 
                            id="tanggal_real" 
                            class="form-control">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Tanggal berakhirnya garansi
                        </small>
                    </div>

                    <div class="alert alert-info mt-2">
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Catatan:</strong> Sistem akan mencatat tanggal pengajuan garansi otomatis 3 hari sebelum tanggal berlaku
                    </div>
                </div>
            </div>

            <!-- Tombol Buat SO -->
            <div class="mt-3">
                <button type="button"
                    class="btn btn-success px-4"
                    id="btnCreateSO"
                    onclick="createSO()">
                    <i class="bi bi-check-circle me-2"></i>Buat SO
                </button>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')

<script>

    let invoiceNumber = null;
    let invoiceData = null;

    // Toggle garansi details
    document.getElementById('hasWarranty').addEventListener('change', function() {
        const warrantyDetails = document.getElementById('warrantyDetails');
        if (this.checked) {
            warrantyDetails.style.display = 'block';
            // Animasi smooth
            warrantyDetails.style.animation = 'fadeIn 0.3s ease';
        } else {
            warrantyDetails.style.display = 'none';
            // Reset nilai garansi
            document.getElementById('type_garansi').value = '';
            document.getElementById('tanggal_real').value = '';
        }
    });

    // Tombol Cari Invoice
    function getInvoice()
    {
        invoiceNumber = document.getElementById('numberInvoice').value.trim();

        if (!invoiceNumber) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Masukkan nomor invoice terlebih dahulu!',
                confirmButtonColor: '#0d9488'
            });
            return;
        }

        // Tampilkan loading
        Swal.fire({
            title: 'Mencari Invoice...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/purchase-invoice/detail?numberInvoice=${invoiceNumber}`)
            .then(res => res.json())
            .then(data => {

                Swal.close();

                if (!data.d || !data.d.detailItem || data.d.detailItem.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invoice Tidak Ditemukan',
                        text: 'Nomor invoice tidak valid atau tidak memiliki detail item',
                        confirmButtonColor: '#0d9488'
                    });
                    return;
                }

                invoiceData = data.d;
                displayInvoiceDetail(data.d);
                showFormStep();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Invoice Ditemukan!',
                    text: `Invoice ${invoiceNumber} berhasil ditemukan`,
                    timer: 1500,
                    showConfirmButton: false
                });
            })
            .catch(err => {
                Swal.close();
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat mencari invoice',
                    confirmButtonColor: '#0d9488'
                });
            });
    }

    // Tampilkan detail invoice
    function displayInvoiceDetail(items)
    {
        let rows = '';
        
        items.detailItem?.forEach(detail => {

            if (detail.detailSerialNumber?.length > 0) {
                detail.detailSerialNumber.forEach(sn => {
                    rows += `
                        <tr>
                            <td class="align-middle">${detail.item?.id ?? '-'}</td>
                            <td class="align-middle">${detail.item?.no ?? '-'}</td>
                            <td class="align-middle">
                                <span class="badge bg-info">${sn.serialNumber?.number ?? '-'}</span>
                            </td>
                            <td class="align-middle">${detail.detailName ?? '-'}</td>
                        </tr>
                    `;
                });
            } else {
                rows += `
                    <tr>
                        <td class="align-middle">${detail.item?.id ?? '-'}</td>
                        <td class="align-middle">${detail.item?.no ?? '-'}</td>
                        <td class="align-middle">
                            <span class="badge bg-secondary">-</span>
                        </td>
                        <td class="align-middle">${detail.detailName ?? '-'}</td>
                    </tr>
                `;
            }
        });

        document.getElementById('invoiceResult').innerHTML = `
            <div class="card mt-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-receipt me-2"></i>
                    <strong>Detail Invoice</strong>
                </div>
                <div class="card-body">

                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Nomor Invoice</th>
                            <td width="10">:</td>
                            <td><strong>${items.number}</strong></td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>:</td>
                            <td>${items.vendor?.name ?? '-'}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>:</td>
                            <td>${items.transDate ?? '-'}</td>
                        </tr>
                    </table>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Item No</th>
                                    <th>Serial Number</th>
                                    <th>Nama Barang</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        `;
    }

    // Tampilkan form setelah invoice ditemukan
    function showFormStep()
    {
        document.getElementById('stepInvoice').style.display = 'none';
        document.getElementById('stepForm').style.display = 'block';
    }

    // Reset ke step awal
    function resetToStepInvoice()
    {
        document.getElementById('stepInvoice').style.display = 'block';
        document.getElementById('stepForm').style.display = 'none';
        document.getElementById('numberInvoice').value = '';
        document.getElementById('invoiceResult').innerHTML = '';
        document.getElementById('description').value = '';
        
        // Reset garansi
        document.getElementById('hasWarranty').checked = false;
        document.getElementById('warrantyDetails').style.display = 'none';
        document.getElementById('type_garansi').value = '';
        document.getElementById('tanggal_real').value = '';
        
        // Reset selects
        if (customerSelect) customerSelect.clear();
        if (warehouseSelect) warehouseSelect.clear();
        if (branchSelect) branchSelect.clear();
    }

    // Tombol Kembali
    function addBackButton()
    {
        const stepForm = document.getElementById('stepForm');
        const existingBackBtn = document.getElementById('backToInvoiceBtn');
        
        if (!existingBackBtn && stepForm.style.display !== 'none') {
            const backBtn = document.createElement('button');
            backBtn.id = 'backToInvoiceBtn';
            backBtn.type = 'button';
            backBtn.className = 'btn btn-outline-secondary mt-3 me-2';
            backBtn.innerHTML = '<i class="bi bi-arrow-left me-2"></i>Cari Invoice Lain';
            backBtn.onclick = resetToStepInvoice;
            
            const btnContainer = document.querySelector('#stepForm .mt-3');
            if (btnContainer) {
                btnContainer.parentNode.insertBefore(backBtn, btnContainer);
            }
        }
    }

    // Setup TomSelect
    let customerSelect = new TomSelect('#customerName', {
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Cari Customer...',
        
        load: function(query, callback) {
            if (!query || query.length < 2) return callback();
            
            fetch(`/customer?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    callback(data.d);
                    // Simpan data customer untuk akses nanti
                    window.customersData = data.d;
                })
                .catch(() => callback());
        },
        
        render: {
            option: function(item, escape) {
                return `
                    <div>
                        <strong>${escape(item.name)}</strong><br>
                        <small class="text-muted">No: ${escape(item.customerNo || '-')}</small>
                    </div>
                `;
            },
            item: function(item, escape) {
                return `<div>${escape(item.name)}</div>`;
            }
        },
        
        // Simpan data customer yang dipilih
        onItemAdd: function(value, item) {
            if (window.customersData) {
                const selected = window.customersData.find(c => c.id == value);
                if (selected) {
                    window.selectedCustomerData = selected;
                    console.log('Customer dipilih:', selected);
                }
            }
        }
    });

    let warehouseSelect = new TomSelect('#warehouseName', {
        preload: true,
        openOnFocus: true,
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        create: false,
        placeholder: 'Cari Gudang...',
        load: function(query, callback) {
            fetch(`/warehouse?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    callback(data.d);
                })
                .catch(() => callback());
        },
        render: {
            option: function(item, escape) {
                return `<div>${escape(item.name)}</div>`;
            },
            item: function(item, escape) {
                return `<div>${escape(item.name)}</div>`;
            }
        }
    });

    let branchSelect = new TomSelect('#branchName', {
        preload: true,
        openOnFocus: true,
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        placeholder: 'Cari Cabang...',
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        load: function(query, callback) {
            fetch(`/branch?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    callback(data.d);
                    this.open();
                })
                .catch(() => callback());
        }
    });

    // Load branch awal
    function loadInitialBranches()
    {
        fetch('/branch')
            .then(res => res.json())
            .then(data => {
                let options = [{ name: 'Pilih Cabang', id: '' }];
                if (data.d) {
                    data.d.forEach(branch => {
                        options.push({ name: branch.name, id: branch.name });
                    });
                }
                branchSelect.clearOptions();
                branchSelect.addOption(options);
                branchSelect.setValue('');
            })
            .catch(err => console.error('Error loading branches:', err));
    }

    loadInitialBranches();

    // Fungsi Create SO
    function createSO()
    {
        let branchName = branchSelect.getValue();
        let warehouseName = warehouseSelect.getValue();
        let customerId = customerSelect.getValue();
        let description = document.getElementById('description').value;
        let hasWarranty = document.getElementById('hasWarranty').checked;
        let typeGaransi = document.getElementById('type_garansi').value;
        let tanggalReal = document.getElementById('tanggal_real').value;

        // Validasi
        if (!branchName) {
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Pilih cabang terlebih dahulu!', confirmButtonColor: '#0d9488' });
            return;
        }

        if (!warehouseName) {
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Pilih gudang terlebih dahulu!', confirmButtonColor: '#0d9488' });
            return;
        }

        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Pilih customer terlebih dahulu!', confirmButtonColor: '#0d9488' });
            return;
        }

        // Validasi garansi jika dicentang
        if (hasWarranty) {
            if (!typeGaransi) {
                Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Pilih tipe garansi!', confirmButtonColor: '#0d9488' });
                return;
            }
            if (!tanggalReal) {
                Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Pilih tanggal berlaku garansi!', confirmButtonColor: '#0d9488' });
                return;
            }
        }

        // Ambil data customer dari TomSelect dengan cara yang benar
        let customerName = '';
        let customerNo = '';
        
        // Cara 1: Menggunakan getOption (jika tersedia)
        if (customerSelect.getOption && customerSelect.getOption(customerId)) {
            let option = customerSelect.getOption(customerId);
            customerName = option.text || '';
            customerNo = option.getAttribute('data-customer-no') || '';
        }
        
        // Cara 2: Menggunakan options collection TomSelect
        if (!customerName && customerSelect.options) {
            // TomSelect menyimpan options di internal
            for (let i = 0; i < customerSelect.options.length; i++) {
                let opt = customerSelect.options[i];
                if (opt.value == customerId) {
                    customerName = opt.text;
                    customerNo = opt.getAttribute('data-customer-no') || '';
                    break;
                }
            }
        }
        
        // Cara 3: Jika masih kosong, ambil dari data yang tersimpan saat load
        if (!customerName && window.selectedCustomerData) {
            customerName = window.selectedCustomerData.name;
            customerNo = window.selectedCustomerData.customerNo;
        }

        // Siapkan data untuk dikirim
        let postData = {
            description: description,
            numberInvoice: invoiceNumber,
            branch_name: branchName,
            warehouse_name: warehouseName,
            customer_no: customerNo,
            customer_name: customerName,
            customer_id: customerId,
            has_warranty: hasWarranty
        };

        // Tambahkan data garansi jika ada
        if (hasWarranty) {
            postData.type_garansi = typeGaransi;
            postData.tanggal_real = tanggalReal;
        }

        console.log('Data yang akan dikirim:', postData);

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah data sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Buat SO!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/second-products/store`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(postData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'SO berhasil dibuat',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan',
                            confirmButtonColor: '#0d9488'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan pada server',
                        confirmButtonColor: '#0d9488'
                    });
                });
            }
        });
    }

    // Tambahkan observer untuk mendeteksi form muncul
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'style') {
                const stepForm = document.getElementById('stepForm');
                if (stepForm.style.display !== 'none') {
                    addBackButton();
                }
            }
        });
    });
    
    observer.observe(document.getElementById('stepForm'), { attributes: true });

    // Tambahkan CSS animasi
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

</script>

@endpush