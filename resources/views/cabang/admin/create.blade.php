@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

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
        --danger: #ef4444;
        --info: #0ea5e9;
        --info-light: #e0f2fe;
        
        --dark: #0f172a;
        --secondary: #64748b;
        --bg-surface: #ffffff;
        --bg-light: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* ===== PANEL CARD ===== */
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
        background: var(--bg-surface);
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

    .panel-body {
        padding: 32px;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-label {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

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
    .modern-input:focus, textarea.form-control:focus, .form-select:focus {
        background: #fff;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    
    textarea.form-control, .form-select {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--bg-light);
        padding: 12px 16px;
        font-size: 14px;
        color: var(--dark);
    }

    /* Switch Custom Checkbox */
    .custom-switch-wrap {
        display: flex;
        align-items: center;
        padding: 16px;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-light);
        cursor: pointer;
        transition: all 0.2s;
    }
    .custom-switch-wrap:hover {
        border-color: var(--primary);
    }
    .form-check-input {
        width: 44px;
        height: 24px;
        margin-top: 0;
        margin-right: 12px;
        cursor: pointer;
    }
    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    /* Detail Garansi Box */
    .warranty-box {
        background: #fff;
        border-left: 4px solid var(--primary);
        border-radius: 0 12px 12px 0;
        padding: 24px;
        margin-top: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        border-top: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    /* Customizing TomSelect */
    .ts-control {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 12px 16px;
        font-size: 14px;
        background: var(--bg-light);
        box-shadow: none;
        transition: all 0.2s;
    }
    .ts-control.focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        background: #fff;
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
    }
    .btn-action-main:hover {
        background: var(--primary-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
    }
    .btn-outline-tool {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-outline-tool:hover {
        background: var(--bg-light);
        color: var(--dark);
        border-color: #cbd5e1;
    }
    .btn-success-main {
        background: var(--success);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14.5px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-success-main:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25);
    }

    /* Inject Table Styles */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-top: 16px;
    }
    .table-modern th {
        background: var(--bg-light);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }
    .table-modern td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .code-badge {
        background: var(--info-light);
        color: var(--info);
        padding: 4px 10px;
        border-radius: 6px;
        font-family: monospace;
        font-weight: 600;
        font-size: 13px;
    }

    /* Animations */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.5s ease backwards; }
</style>
@endpush

<div class="container-fluid py-4">

    <div class="panel-card animate-card" style="animation-delay: 0.1s">
        <div class="panel-body">
            
            <!-- Step 1: Cari Faktur -->
            <div id="stepInvoice">
                <label class="form-label text-secondary mb-3">Nomor Faktur Penjualan</label>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div class="flex-grow-1 modern-input-group" style="min-width: 250px;">
                        <i class="bi bi-receipt"></i>
                        <input type="text"
                            id="numberInvoice"
                            class="modern-input"
                            placeholder="Contoh: PI.2026.**.****">
                    </div>
                    
                    <button type="button" class="btn-action-main" onclick="getInvoice()">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    
                    <a href="{{ Auth::check() && Auth::user()->status === 'admin' ? route('second.index') : route('second.indexKaryawan') }}" class="btn-outline-tool">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="form-text mt-2 ms-1 text-muted">
                    <i class="bi bi-info-circle me-1"></i> Masukkan nomor faktur penjualan yang valid untuk memuat data item.
                </div>
            </div>

            <!-- Step 2: Detail Faktur & Form (Hidden dulu) -->
            <div id="stepForm" style="display: none;">
                
                <!-- Detail Faktur Disini -->
                <div id="invoiceResult"></div>

                <!-- Form Input Lainnya -->
                <div class="mt-5 pt-4 border-top">
                    <h5 class="mb-4 fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-pencil-square text-primary me-2"></i> Informasi Tambahan
                    </h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select id="customerName" class="form-control">
                                <option value="">Pilih Customer...</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Keterangan Tambahan</label>
                            <textarea
                                name="description"
                                id="description"
                                rows="1"
                                class="form-control"
                                placeholder="Masukkan keterangan tambahan jika ada..."></textarea>
                        </div>
                    </div>

                    <!-- Sisa Garansi -->
                    <div class="mt-4">
                        <label class="custom-switch-wrap form-check form-switch mb-0" for="hasWarranty">
                            <input type="checkbox" class="form-check-input" id="hasWarranty" name="has_warranty" value="1">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-shield-check text-primary me-2"></i> Produk Memiliki Garansi</div>
                                <div class="text-muted small ms-4 mt-1">Aktifkan jika barang ini masih memiliki sisa masa garansi aktif.</div>
                            </div>
                        </label>
                    </div>

                    <!-- Detail Garansi (Hidden by default) -->
                    <div id="warrantyDetails" style="display: none;" class="warranty-box">
                        <h6 class="mb-3 fw-bold text-dark">Lengkapi Data Garansi</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipe Garansi</label>
                                <select name="type_garansi" id="type_garansi" class="form-select">
                                    <option value="">-- Pilih Tipe Garansi --</option>
                                    <option value="resmi">Resmi</option>
                                    <option value="distributor">Distributor</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Berlaku Sampai Tanggal</label>
                                <input type="date" name="tanggal_real" id="tanggal_real" class="form-control px-3">
                            </div>
                        </div>

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mt-3 mb-0 d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill mt-1"></i>
                            <div class="small fw-medium">Sistem akan mencatat pengajuan garansi otomatis 3 hari sebelum masa berlaku habis.</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-5 pt-3 d-flex gap-3 flex-wrap" id="actionButtonsArea">
                    <button type="button" class="btn-success-main" id="btnCreateSO" onclick="createSO()">
                        <i class="bi bi-check2-circle fs-5"></i> Simpan & Buat SO
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let invoiceNumber = null;
    let invoiceData = null;

    // Toggle garansi details
    document.getElementById('hasWarranty').addEventListener('change', function() {
        const warrantyDetails = document.getElementById('warrantyDetails');
        if (this.checked) {
            warrantyDetails.style.display = 'block';
            warrantyDetails.style.animation = 'slideUpFade 0.3s ease';
        } else {
            warrantyDetails.style.display = 'none';
            document.getElementById('type_garansi').value = '';
            document.getElementById('tanggal_real').value = '';
        }
    });

    // Tombol Cari Faktur
    function getInvoice()
    {
        invoiceNumber = document.getElementById('numberInvoice').value.trim();

        if (!invoiceNumber) {
            Swal.fire({
                icon: 'warning',
                title: 'Kolom Kosong',
                text: 'Silakan masukkan nomor Faktur Penjualan terlebih dahulu!',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        Swal.fire({
            title: 'Mencari Faktur Penjualan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/price-submission/detail?numberInvoice=${invoiceNumber}`)
            .then(res => res.json())
            .then(data => {

                Swal.close();

                if (!data.d || !data.d.detailItem || data.d.detailItem.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Faktur Tidak Ditemukan',
                        text: 'Nomor Faktur Penjualan tidak valid atau tidak memiliki detail item.',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }

                invoiceData = data.d;
                displayInvoiceDetail(data.d);
                showFormStep();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Faktur Ditemukan!',
                    text: `Faktur Penjualan ${invoiceNumber} berhasil dimuat.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            })
            .catch(err => {
                Swal.close();
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal mencari Faktur Penjualan. Periksa koneksi Anda.',
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    // Tampilkan detail Faktur Penjualan
    function displayInvoiceDetail(items)
    {
        let rows = '';
        
        items.detailItem?.forEach(detail => {
            if (detail.detailSerialNumber?.length > 0) {
                detail.detailSerialNumber.forEach(sn => {
                    rows += `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selected-item m-0" 
                                       value="${detail.item?.no}" 
                                       data-detail='${encodeURIComponent(JSON.stringify(detail))}'>
                            </td>
                            <td><span class="code-badge">${detail.item?.no ?? '-'}</span></td>
                            <td><strong class="text-dark">${detail.detailName ?? '-'}</strong></td>
                            <td><span class="code-badge text-primary bg-primary bg-opacity-10 border border-primary border-opacity-25">${sn.serialNumber?.number ?? '-'}</span></td>
                        </tr>
                    `;
                });
            } else {
                rows += `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input selected-item m-0" 
                                   value="${detail.item?.no}" 
                                   data-detail='${encodeURIComponent(JSON.stringify(detail))}'>
                        </td>
                        <td><span class="code-badge">${detail.item?.no ?? '-'}</span></td>
                        <td><strong class="text-dark">${detail.detailName ?? '-'}</strong></td>
                        <td><span class="text-muted fst-italic">Tanpa SN</span></td>
                    </tr>
                `;
            }
        });

        document.getElementById('invoiceResult').innerHTML = `
            <div class="mt-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark m-0">
                        <i class="bi bi-receipt text-primary me-2"></i> Detail Faktur Penjualan
                    </h5>
                </div>

                <div class="bg-light rounded-3 p-4 border border-light mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="text-secondary small fw-semibold mb-1">Nomor Faktur</div>
                            <div class="fw-bold text-dark fs-6">${items.number}</div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="text-secondary small fw-semibold mb-1">Customer / Vendor</div>
                            <div class="fw-bold text-dark fs-6">${items.vendor?.name ?? '-'}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary small fw-semibold mb-1">Tanggal Transaksi</div>
                            <div class="fw-bold text-dark fs-6">${items.transDate ?? '-'}</div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Daftar Item pada Faktur</h6>
                <div class="table-responsive rounded-3 border">
                    <table class="table table-modern table-hover m-0">
                        <thead>
                            <tr>
                                <th width="60" class="text-center">
                                    <input type="checkbox" class="form-check-input m-0" id="checkAll">
                                </th>
                                <th width="150">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="200">Serial Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        // Check All listener
        const checkAll = document.getElementById('checkAll');
        if(checkAll){
            checkAll.addEventListener('change', function(){
                const checkboxes = document.querySelectorAll('.selected-item');
                checkboxes.forEach(cb => cb.checked = checkAll.checked);
            });
        }
    }

    function showFormStep()
    {
        document.getElementById('stepInvoice').style.display = 'none';
        const stepForm = document.getElementById('stepForm');
        stepForm.style.display = 'block';
        stepForm.style.animation = 'slideUpFade 0.4s ease';
    }

    function resetToStepInvoice()
    {
        document.getElementById('stepInvoice').style.display = 'block';
        document.getElementById('stepForm').style.display = 'none';
        
        document.getElementById('numberInvoice').value = '';
        document.getElementById('invoiceResult').innerHTML = '';
        document.getElementById('description').value = '';
        
        document.getElementById('hasWarranty').checked = false;
        document.getElementById('warrantyDetails').style.display = 'none';
        document.getElementById('type_garansi').value = '';
        document.getElementById('tanggal_real').value = '';
        
        if (customerSelect) customerSelect.clear();
    }

    // Injeksi tombol kembali setelah "Buat SO"
    function addBackButton()
    {
        const existingBackBtn = document.getElementById('backToInvoiceBtn');
        
        if (!existingBackBtn && document.getElementById('stepForm').style.display !== 'none') {
            const backBtn = document.createElement('button');
            backBtn.id = 'backToInvoiceBtn';
            backBtn.type = 'button';
            backBtn.className = 'btn-outline-tool';
            backBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise fs-5"></i> Cari Faktur Lain';
            backBtn.onclick = resetToStepInvoice;
            
            document.getElementById('actionButtonsArea').appendChild(backBtn);
        }
    }

    // TomSelect Customer
    let customerSelect = new TomSelect('#customerName', {
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Ketik nama customer...',
        preload: true,
        
        load: function(query, callback) {
            fetch(`/customer/manual?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    callback(data.d);
                    window.customersData = data.d;
                })
                .catch(() => callback());
        },
        
        render: {
            option: function(item, escape) {
                return `
                    <div class="py-2 px-3">
                        <div class="fw-bold text-dark">${escape(item.name)}</div>
                        <div class="text-muted small">${escape(item.customer_number || '-')}</div>
                    </div>
                `;
            },
            item: function(item, escape) {
                return `<div class="fw-medium">${escape(item.name)}</div>`;
            }
        },
        
        onItemAdd: function(value, item) {
            if (window.customersData) {
                const selected = window.customersData.find(c => c.id == value);
                if (selected) {
                    window.selectedCustomerData = selected;
                }
            }
        }
    });

    // Buat SO
    function createSO()
    {
        let customerId = customerSelect.getValue();
        let description = document.getElementById('description').value;
        let hasWarranty = document.getElementById('hasWarranty').checked;
        let typeGaransi = document.getElementById('type_garansi').value;
        let tanggalReal = document.getElementById('tanggal_real').value;

        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih Customer terlebih dahulu!', confirmButtonColor: '#4f46e5' });
            return;
        }

        if (hasWarranty) {
            if (!typeGaransi) {
                Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih Tipe Garansi!', confirmButtonColor: '#4f46e5' });
                return;
            }
            if (!tanggalReal) {
                Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih tanggal berlaku garansi!', confirmButtonColor: '#4f46e5' });
                return;
            }
        }

        let customerName = '';
        let customerNo = '';
        
        // Membaca data Customer secara presisi menggunakan properti window hasil onItemAdd
        if (window.selectedCustomerData && window.selectedCustomerData.id == customerId) {
            customerName = window.selectedCustomerData.name;
            customerNo = window.selectedCustomerData.customer_number; 
        } else {
            let option = customerSelect.getOption(customerId);
            if (option) {
                customerName = option.innerText.split('\n')[0].trim();
            }
        }
        
        // Proteksi di sisi client agar tidak mengirim customer_no kosong ke server
        if (!customerNo) {
            Swal.fire({
                icon: 'error',
                title: 'Data Customer Tidak Valid',
                text: 'Sistem gagal mendeteksi Kode Pelanggan (customer_no). Silakan hapus dan pilih ulang nama customer.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        let selectedItems = [];
        document.querySelectorAll('.selected-item:checked').forEach(el => {
            selectedItems.push(JSON.parse(decodeURIComponent(el.dataset.detail)));
        });

        if (selectedItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Kosong',
                text: 'Pilih minimal 1 item barang yang ingin diajukan harganya.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        let postData = {
            description: description,
            numberInvoice: invoiceNumber,
            customer_no: customerNo,
            customer_name: customerName,
            customer_id: customerId,
            has_warranty: hasWarranty,
            items: selectedItems,
        };

        if (hasWarranty) {
            postData.type_garansi = typeGaransi;
            postData.tanggal_real = tanggalReal;
        }

        Swal.fire({
            title: 'Konfirmasi Data',
            text: 'Apakah semua data pengajuan harga sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Buat SO Sekarang',
            cancelButtonText: 'Batal Periksa Lagi'
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'Memproses Data...',
                    text: 'Menyimpan Pengajuan Harga & Membuat SO',
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
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        // Melempar error spesifik dari Accurate / Exception Laravel
                        throw new Error(data.message || 'Terjadi masalah pada sistem server.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Sales Order (SO) berhasil dibuat.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Proses pembuatan SO Accurate gagal.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembuatan SO Gagal',
                        text: err.message,
                        confirmButtonColor: '#ef4444'
                    });
                });
            }
        });
    }

    // Observer form muncul
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

</script>
@endpush
@endsection