<script>
document.addEventListener('DOMContentLoaded', () => {

    const itemId = "{{ $item['id'] }}";
    const session = "{{ $session }}";

    // =====================================================
    // 2. TOM SELECT - BRANCH
    // =====================================================
    let branchSelect = new TomSelect("#branchSelect", {
        valueField: "name",
        labelField: "name",
        searchField: "name",
        preload: true,
        load: function(query, callback) {
            fetch(`/branches?page=1`)
                .then(res => res.json())
                .then(json => callback(json.data))
                .catch(() => callback());
        }
    });


    // =====================================================
    // 3. TOM SELECT - WAREHOUSE FILTER
    // =====================================================
    let warehouseSelect = new TomSelect("#warehouseFilter", {
        plugins: ["remove_button"],
        maxItems: null,
        persist: false,
        hideSelected: true,
    });


    // =====================================================
    // 4. FILTER CABANG → UPDATE HARGA
    // =====================================================
    branchSelect.on("change", value => {

        const spinner = document.getElementById("priceSpinner");
        spinner.classList.remove("d-none");

        fetch(`/karyawan/${itemId}/price?branchName=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(data => {

                // ===========================
                // 1. UPDATE HARGA GLOBAL (tanpa unit)
                // ===========================
                const userGlobal = document.getElementById("userPriceMain");
                const resellerGlobal = document.getElementById("resellerPriceMain");

                if (userGlobal) userGlobal.textContent = formatRupiah(data.user);
                if (resellerGlobal) resellerGlobal.textContent = formatRupiah(data.reseller);

                // ===========================
                // 2. UPDATE HARGA PER UNIT (PACK / PCS / dll)
                // ===========================
                document.querySelectorAll(".price-user").forEach(el => {
                    const unit = el.dataset.unit; // misal: PCS, PACK

                    const priceObj = data?.unitPrices?.[unit];

                    if (priceObj && priceObj.user) {
                        el.textContent = `${formatRupiah(priceObj.user)} / ${unit}`;
                    }
                });

                document.querySelectorAll(".price-reseller").forEach(el => {
                    const unit = el.dataset.unit; // misal: PCS, PACK

                    const priceObj = data?.unitPrices?.[unit];

                    if (priceObj && priceObj.reseller) {
                        el.textContent = `${formatRupiah(priceObj.reseller)} / ${unit}`;
                    }
                });
            })
            .finally(() => spinner.classList.add("d-none"));
    });


    function formatRupiah(n) {
        return "Rp " + parseInt(n || 0).toLocaleString("id-ID");
    }


    // =====================================================
    // 5. FILTER GUDANG
    // =====================================================
    warehouseSelect.on("change", values => {
        document.querySelectorAll('[id^="warehouse_"]').forEach(card => {
            const type = card.id.replace("warehouse_", "");
            card.style.display = (values.length === 0 || values.includes(type))
                ? "block"
                : "none";
        });
    });

    /* ============================================================
        6. UPDATE VISIBILITAS ROW (hide jika stok 0)
    ============================================================ */
    function updateRowVisibility(tdElement, newStock) {
        const tr = tdElement.closest("tr");
        if (!tr) return;

        tr.style.display = newStock <= 0 ? "none" : "";
    }

    /* ============================================================
    7. UPDATE TOTAL
    ============================================================ */
    function updateTotals() {
        const groups = ["store", "tsc", "reseller", "konsinyasi", "panda"];

        groups.forEach(group => {
            let total = 0;
            const rows = document.querySelectorAll(`#warehouse_${group} tbody tr`);

            rows.forEach(row => {
                if (row.style.display !== "none") {
                    const tdStock = row.querySelector('[id^="stock_"]');
                    if (tdStock) {
                        total += parseFloat(tdStock.textContent) || 0;
                    }
                }
            });

            const totalSpan = document.getElementById(`total_${group}`);
            if (totalSpan) totalSpan.textContent = total.toLocaleString("id-ID");
        });
    }

    function updateWarehouseVisibility() {
        const groups = ["store", "tsc", "reseller", "konsinyasi", "panda"];

        groups.forEach(group => {
            const table = document.getElementById(`warehouse_${group}`);
            if (!table) return;

            const rows = table.querySelectorAll("tbody tr");

            // Hitung berapa row yang masih tampil
            const visibleRows = Array.from(rows).filter(
                row => row.style.display !== "none"
            );

            // Jika tidak ada row yang tampil → sembunyikan tabel
            table.style.display = visibleRows.length === 0 ? "none" : "block";
        });
    }

    /* ============================================================
    8. REALTIME STOCK UPDATE
    ============================================================ */
    function updateRealtimeStock() {
        return new Promise(resolve => {
            const rows = document.querySelectorAll('[id^="stock_"]');
            let done = 0;

            if (rows.length === 0) return resolve();

            rows.forEach(row => {
                //Loading
                row.classList.add('stock-loading')
                const warehouseName = row.closest("tr").children[0].innerText.trim();
                const branchName = document.querySelector("#branchSelect")?.value || '';

                fetch(`/ajax/warehouse-stock?id={{ $item['id'] }}&warehouse=${encodeURIComponent(warehouseName)}&branchName=${encodeURIComponent(branchName)}`)
                    .then(res => res.json())
                    .then(json => {
                        if (json.stock !== undefined) {
                            row.textContent = json.stock;
                            updateRowVisibility(row, json.stock);
                        }
                    })
                    .finally(() => {
                        row.classList.remove('stock-loading')
                        done++;
                        if (done === rows.length) resolve();
                    });
            });
        });
    }

    /* ============================================================
    9. JALANKAN SEKALI + INTERVAL ANTI-DUPLIKAT
    ============================================================ */

    // 🛑 CEGAH INTERVAL BERLIPAT-LIPAT
    if (!window.stockUpdaterRunning) {
        window.stockUpdaterRunning = true;

        // First run
        updateRealtimeStock().then(() => {
            updateTotals();
            updateWarehouseVisibility();
        });

        // Every 30 sec
        setInterval(() => {
            updateRealtimeStock()
                .then(() => {
                    updateTotals();
                    updateWarehouseVisibility(); // ⬅ tambah ini juga
                });
        }, 120000);
    }

    // ===========================================================
    // 10. FILTER HARGA 
    // ===========================================================

    const selector = document.getElementById("priceType");

    const userBox = document.getElementById("userPriceBox");
    const resellerBox = document.getElementById("resellerPriceBox");
    const partnerBox = document.getElementById("partnerPriceBox");

    function updatePriceView() {
        let mode = selector.value;

        if (mode === "all") {
            userBox.style.display = "block";
            resellerBox.style.display = "block";
            partnerBox.style.display = "block";
        } else if (mode === "user") {
            userBox.style.display = "block";
            resellerBox.style.display = "none";
            partnerBox.style.display = "none";
        } else if (mode === "reseller") {
            userBox.style.display = "none";
            resellerBox.style.display = "block";
            partnerBox.style.display = "none";
        } else if (mode === 'partner') {
            userBox.style.display = "none";
            resellerBox.style.display = "none";
            partnerBox.style.display = "block";
        }
    }

    // trigger awal
    updatePriceView();

    // trigger saat select berubah
    selector.addEventListener("change", updatePriceView);

});

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        showCopyNotif("Berhasil dicopy!");
    }).catch(err => {
        showCopyNotif("Gagal copy!", true);
        console.error('Failed to copy', err);
    });
}

function showCopyNotif(message, isError = false) {
    const notif = document.createElement("div");
    notif.innerText = message;

    notif.style.position = "fixed";
    notif.style.top = "20px";
    notif.style.right = "20px";
    notif.style.padding = "10px 16px";
    notif.style.borderRadius = "8px";
    notif.style.color = "#fff";
    notif.style.fontSize = "14px";
    notif.style.zIndex = "9999";
    notif.style.backgroundColor = isError ? "#dc3545" : "#28a745";
    notif.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";
    notif.style.opacity = "0";
    notif.style.transition = "opacity 0.3s";

    document.body.appendChild(notif);

    setTimeout(() => notif.style.opacity = "1", 10);
    setTimeout(() => {
        notif.style.opacity = "0";
        setTimeout(() => notif.remove(), 300);
    }, 1500);
}

function changeImage(thumb, src) {
    // Update main image
    document.getElementById('mainImage').src = src;
    
    // Update active thumb
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

// === 📄 Export PDF Detail (otomatis sesuai filter aktif) ===
document.addEventListener('click', function(e) {
    const btn = e.target.closest('#btn-export-pdf');
    if (!btn) return;
    e.preventDefault();

    const itemId = "{{ $item['id'] }}";
    const encryptedId = "{{ request()->route('encrypted') }}"; // ambil dari URL

    // Ambil semua filter aktif
    const branchSelect = document.querySelector('#branchSelect');
    const priceTypeSelect = document.querySelector('#priceType');
    const warehouseSelect = document.querySelector('#warehouseFilter');

    const branchName = branchSelect ? branchSelect.value : '';
    const priceType = priceTypeSelect ? priceTypeSelect.value : 'all';
    const warehouses = warehouseSelect ? warehouseSelect.tomselect.getValue() : [];

    // Susun URL
    const params = new URLSearchParams();
    if (branchName) params.append('branchName', branchName);
    if (priceType) params.append('priceType', priceType);
    warehouses.forEach(w => params.append('warehouses[]', w));

    const pdfUrl = `/karyawan/${encryptedId}/export-pdf?${params.toString()}`;

    // Buka di tab baru
    window.open(pdfUrl, '_blank');
});

    document
    .querySelectorAll('.preview-image')
    .forEach(img => {

        img.addEventListener('click', function() {

            document
                .getElementById('modalImage')
                .src = this.dataset.image;
        });
    });
</script>
<script type="module">

import PhotoSwipeLightbox from 'https://cdn.jsdelivr.net/npm/photoswipe@5/dist/photoswipe-lightbox.esm.min.js';

const lightbox = new PhotoSwipeLightbox({

    gallery: '#product-gallery',

    children: 'a',

    pswpModule: () =>
        import('https://cdn.jsdelivr.net/npm/photoswipe@5/dist/photoswipe.esm.min.js')
});

lightbox.init();

</script>