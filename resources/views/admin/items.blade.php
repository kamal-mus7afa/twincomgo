@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Barang & Jasa')

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
        
        --dark: #0f172a;
        --secondary: #64748b;
        --bg-surface: #ffffff;
        --bg-light: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }
    .header-content h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 6px;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-description {
        color: var(--secondary);
        margin: 0;
        font-size: 14.5px;
    }

    /* ===== PANEL CARD ===== */
    .panel-card {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        overflow: visible; /* Important for TomSelect dropdowns */
        margin-bottom: 24px;
    }
    .panel-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        background: var(--bg-surface);
        border-radius: 16px 16px 0 0;
    }
    .panel-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .panel-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .panel-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 2px 0;
    }
    .panel-subtitle {
        font-size: 13px;
        color: var(--secondary);
        margin: 0;
    }
    .panel-body {
        padding: 24px;
    }
    .panel-footer {
        padding: 16px 24px;
        background: var(--bg-light);
        border-top: 1px solid var(--border-color);
        border-radius: 0 0 16px 16px;
    }

    /* ===== BUTTONS ===== */
    .btn-outline-tool {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
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
    .btn-export {
        background: white;
        color: var(--danger);
        border: 1px solid var(--danger-light);
    }
    .btn-export:hover {
        background: var(--danger-light);
        color: var(--danger);
        border-color: var(--danger);
    }
    
    /* Tombol Search & Reset di dalam partials filter */
    .search-btn {
        background: var(--dark);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s;
        border: none;
    }
    .search-btn:hover { background: #1e293b; color: white; transform: translateY(-1px); }
    .reset-btn {
        background: white;
        color: var(--secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 20px;
        transition: all 0.2s;
        font-weight: 600;
    }
    .reset-btn:hover { background: #f1f5f9; color: var(--dark); }

    /* ===== MODERN TABLE (FOR PARTIALS) ===== */
    .table-container {
        width: 100%;
        overflow-x: auto;
        max-height: 600px;
    }
    .table { margin: 0; width: 100%; border-collapse: separate; border-spacing: 0; }
    .table thead th {
        background: var(--bg-light);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table tbody tr { transition: background 0.2s; }
    .table tbody tr:hover td { background: var(--bg-light); }
    .table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .table tbody tr:last-child td { border-bottom: none; }

    .td-harga {
        width: 150px;
        text-align: right;
        vertical-align: middle;
    }
    .harga-grid {
        display: grid;
        grid-template-columns: 30px 1fr;
        justify-content: end;
        align-items: center;
        font-weight: 600;
    }
    .harga-rp { text-align: left; color: var(--secondary); font-size: 12px; }
    .harga-nominal { text-align: right; color: var(--success); }

    /* Product Images */
    .product-image-sm {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--border-color);
    }
    .product-image-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: var(--bg-light);
        border: 1px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--secondary);
        font-weight: 600;
        font-size: 18px;
    }

    /* Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-available { background: var(--success-light); color: var(--success); }
    .badge-lowstock { background: var(--warning-light); color: #d97706; }
    .badge-soldout { background: var(--danger-light); color: var(--danger); }

    /* Controls */
    .per-page-select {
        border-radius: 10px;
        border: 1px solid var(--border-color);
        padding: 6px 30px 6px 12px;
        font-size: 13px;
        font-weight: 600;
        background-color: var(--bg-light);
        color: var(--dark);
    }

    /* ===== PAGINATION (FOR PARTIALS) ===== */
    .pagination-info { font-size: 13.5px; color: var(--secondary); }
    .pagination { margin: 0; gap: 4px; }
    .page-link {
        border-radius: 8px !important;
        border: 1px solid transparent;
        padding: 6px 12px;
        color: var(--secondary);
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }
    .page-link:hover {
        background: var(--bg-light);
        border-color: var(--border-color);
        color: var(--dark);
    }
    .page-item.active .page-link {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .page-item.disabled .page-link { color: #cbd5e1; background: transparent; }

    /* ===== LOADING OVERLAY ===== */
    #item-container { position: relative; min-height: 200px; }
    .loading-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(4px);
        z-index: 100;
        border-radius: 0 0 16px 16px;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-card { animation: slideUpFade 0.5s ease backwards; }

    /* ===== RESPONSIVE & MOBILE LIST ===== */
    @media (max-width: 767.98px) {
        body { font-size: 13px; }
        .page-header { text-align: left; }
        
        .panel-card { border-radius: 16px; border: none; box-shadow: none; background: transparent; margin-bottom: 16px; }
        .panel-header { background: #fff; border-radius: 16px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .panel-body { background: transparent; padding: 0; }
        
        /* Mobile Product Card */
        .desktop-table { display: none !important; }
        .product-card {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            margin-bottom: 12px;
        }
        .product-card .top-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .product-title {
            flex: 1 1 70%;
            font-size: 13px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.4;
        }
        .harga-grid { flex: 0 0 30%; text-align: right; }
        .harga-rp { font-size: 11px; }
        .harga-nominal { font-size: 13px; color: var(--success); }
        .product-code { font-size: 11px; color: var(--secondary); font-family: monospace; background: var(--bg-light); padding: 2px 6px; border-radius: 4px; }
        .product-meta { font-size: 11px; color: var(--secondary); margin-top: 8px; }
        
        /* Form & Controls tweaks */
        .per-page-select { width: 70px !important; padding: 4px 20px 4px 8px; font-size: 12px; }
        
        .pagination-section { background: transparent; padding: 16px 0; border: none; }
        .pagination { flex-wrap: wrap; justify-content: center; gap: 6px; }
        .pagination .page-link-ajax { min-width: 40px; text-align: center; background: #fff; border: 1px solid var(--border-color); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    }
    @media (min-width: 768px) {
        .mobile-list { display: none; }
    }
</style>
@endpush

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card" style="animation-delay: 0.1s">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 w-100">
            <div class="header-content">
                <h1>
                    <i class="bi bi-box-seam text-primary me-2"></i> Barang & Jasa
                </h1>
                <p class="page-description">Kelola, cari, dan pantau seluruh data inventori produk di sistem.</p>
            </div>
        </div>
    </div>

    {{-- FILTER SECTION --}}
    <div class="panel-card animate-card" style="animation-delay: 0.2s">
        <div class="panel-header">
            <div class="panel-header-left">
                <div class="panel-icon">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <div>
                    <h2 class="panel-title">Filter & Pencarian</h2>
                    <p class="panel-subtitle">Gunakan filter di bawah untuk menemukan produk spesifik.</p>
                </div>
            </div>
            
            {{-- DESKTOP EXPORT --}}
            <div class="d-none d-md-block">
                <a href="#" id="btn-export-pdf" class="btn-outline-tool btn-export" data-export-url="{{ route('items.exportPdf') }}">
                    <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        
        <div class="panel-body bg-light">
            @include('items.partials.filter')
            
            {{-- MOBILE EXPORT --}}
            <div class="d-md-none mt-3">
                <a href="#" id="btn-export-pdf-mobile" class="btn-outline-tool btn-export w-100" data-export-url="{{ route('items.exportPdf') }}">
                    <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="panel-card animate-card" style="animation-delay: 0.3s">
        <div class="panel-header">
            <div class="panel-header-left">
                <div class="panel-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <h2 class="panel-title">Daftar Produk</h2>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small fw-semibold d-none d-sm-inline">Tampilkan:</span>
                <select id="per_page" class="form-select per-page-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>

        {{-- PRODUCTS CONTAINER --}}
        <div id="item-container" class="table-container">
            @include('items.partials.item-table', ['items' => $items])
        </div>

        {{-- PAGINATION --}}
        <div id="pagination-container" class="panel-footer">
            @include('items.partials.pagination', [
                'page' => $page,
                'pageCount' => $pageCount,
                'queryParams' => request()->except('page')
            ])
        </div>
    </div>
</div>

{{-- TOAST NOTIFICATION --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000">
    <div id="toastFilterError" class="toast align-items-center border-0 bg-warning bg-opacity-10" role="alert">
        <div class="d-flex border border-warning border-opacity-50 rounded-3 p-1">
            <div class="toast-body fw-semibold text-dark d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                <div>
                    Filter harga terlalu sempit.<br>
                    <span class="text-secondary fw-normal small">Silakan perbesar rentang harganya.</span>
                </div>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemContainer        = document.getElementById("item-container");
    const paginationContainer  = document.getElementById("pagination-container");
    const filterForm           = document.getElementById("filter-form");
    const perPageSelect        = document.getElementById("per_page");

    // ========= DETEKSI USER FORCE DARI URL =========
    const urlParams = new URL(window.location.href).searchParams;
    if (urlParams.get("force") === "1") {
        localStorage.setItem("force_item_queue", "1");
    }

    // Helper: apakah user ini termasuk "user antrian" (force mode)?
    function isForceUser() {
        return localStorage.getItem("force_item_queue") === "1";
    }

    // Helper: apply force=1 ke URL kalau user antrian
    function applyForceToUrl(rawUrl) {
        const u = new URL(rawUrl, window.location.origin);
        if (isForceUser()) {
            u.searchParams.set("force", "1");
        }
        return u;
    }

    let currentPage = parseInt(urlParams.get("page") || "1", 10);
    let isLoading = false;

    // ==============================
    // INIT TOM-SELECT (jika ada)
    // ==============================
    const categorySelect = document.getElementById("category_search");
    if (categorySelect && !categorySelect.dataset.tsInit) {
        categorySelect.dataset.tsInit = "1";
        new TomSelect("#category_search", {
            valueField: "id",
            create: false,
            labelField: "text",
            plugins: ["remove_button"],
            searchField: "text",
            maxOptions: 9999,
            allowEmptyOption: false,
            onChange(value) {
                const hidden = document.getElementById("itemCategoryId");
                if (hidden) hidden.value = value || "";
                submitFilterAjax();
            },
        });
    }

    // =================================================
    // FUNGSI SHOW/HIDE LOADER
    // =================================================
    function showInlineLoader() {
        if (!itemContainer) return;
        let overlay = itemContainer.querySelector(".loading-overlay");
        if (!overlay) {
            overlay = document.createElement("div");
            overlay.className = "loading-overlay d-flex flex-column justify-content-center align-items-center";
            overlay.innerHTML = `<div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>`;
            itemContainer.style.position = "relative";
            itemContainer.appendChild(overlay);
        }
        overlay.style.display = "flex";
    }

    function hideInlineLoader() {
        if (!itemContainer) return;
        const overlay = itemContainer.querySelector(".loading-overlay");
        if (overlay) overlay.style.display = "none";
    }

    // ===========================================
    // FUNGSI UTAMA LOAD PAGE VIA AJAX
    // ===========================================
    async function loadPage(url, options = {}) {
        if (!itemContainer || !paginationContainer) return;
        if (isLoading) return;
        isLoading = true;
        showInlineLoader();

        try {
            const urlObj = applyForceToUrl(url);
            currentPage = parseInt(urlObj.searchParams.get("page") || "1", 10);

            const response = await fetch(urlObj.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            if (!response.ok) {
                const urlTest = new URL(url, window.location.origin);
                const minP = urlTest.searchParams.get("min_price");
                const maxP = urlTest.searchParams.get("max_price");

                if ((minP || maxP) && response.status === 500) {
                    hideInlineLoader();
                    isLoading = false;
                    const overlays = itemContainer.querySelectorAll(".loading-overlay");
                    overlays.forEach(o => o.remove());
                    showFilterToast();
                    return;
                }
                throw new Error("HTTP " + response.status);
            }

            const html = await response.text();
            const wrapper = document.createElement("div");
            wrapper.innerHTML = html;

            const newItemContainer = wrapper.querySelector("#item-container");
            const newPagination = wrapper.querySelector("#pagination-container");

            if (newItemContainer) {
                itemContainer.innerHTML = newItemContainer.innerHTML;
                reloadPricesAfterAjax();
            }
            if (newPagination) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }

            if (!options.skipHistory) {
                history.pushState({ url: urlObj.toString() }, "", urlObj.toString());
            }

            if (!options.noScroll) {
                // Smooth scroll ke atas container tabel
                const tableCard = document.querySelector('.table-container').parentElement;
                window.scrollTo({ top: tableCard.offsetTop - 20, behavior: "smooth" });
            }
        } catch (err) {
            console.error(err);
            const overlays = itemContainer.querySelectorAll(".loading-overlay");
            overlays.forEach(o => o.remove());
        } finally {
            hideInlineLoader();
            isLoading = false;
        }
    }

    // ==================================
    // SUBMIT FILTER DENGAN AJAX
    // ==================================
    function buildFilterUrl() {
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : {});
        
        if (perPageSelect) {
            params.set("per_page", perPageSelect.value);
        }
        if (isForceUser()) {
            params.set("force", "1");
        }

        return "{{ route('admin.items') }}?" + params.toString();
    }

    function submitFilterAjax() {
        const url = buildFilterUrl();
        loadPage(url);
    }

    // Event Listeners
    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            submitFilterAjax();
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener("change", function () {
            submitFilterAjax();
        });
    }

    // ==================================
    // EXPORT PDF
    // ==================================
    document.addEventListener("click", function (e) {
        const btn = e.target.closest("#btn-export-pdf, #btn-export-pdf-mobile");
        if (!btn) return;

        e.preventDefault();
        if (!filterForm) {
            window.open(btn.dataset.exportUrl, "_blank");
            return;
        }

        const params = new URLSearchParams(new FormData(filterForm));
        if (perPageSelect) {
            params.set("per_page", perPageSelect.value);
        }
        params.set("page", currentPage.toString());

        const pdfUrl = `${btn.dataset.exportUrl}?${params.toString()}`;
        window.open(pdfUrl, "_blank");
    });

    // ==================================
    // PAGINATION KLIK AJAX
    // ==================================
    document.addEventListener("click", function (e) {
        const link = e.target.closest(".page-link-ajax");
        if (!link) return;
        e.preventDefault();
        loadPage(link.href);
    });

    // ==================================
    // BROWSER BACK/FORWARD
    // ==================================
    window.addEventListener("popstate", function (e) {
        if (e.state && e.state.url) {
            loadPage(e.state.url, { skipHistory: true, noScroll: true });
        }
    });
});

// =========================================================
// LAZY PRICE ENGINE
// =========================================================
function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID").format(angka);
}

function collectLazyPrices() {
    const map = {};
    document.querySelectorAll("[data-lazy-price]").forEach(el => {
        const id = el.dataset.id;
        const mode = el.dataset.mode;
        if (id && !map[id]) {
            map[id] = { id, mode };
        }
    });
    window.lazyPrices = Object.values(map);
}

async function loadPrices() {
    if (!window.lazyPrices || window.lazyPrices.length === 0) return;
    const forceMode = localStorage.getItem("force_item_queue") === "1";

    for (let item of window.lazyPrices) {
        let targets = document.querySelectorAll(`[data-id="${item.id}"][data-lazy-price]`);
        if (targets.length === 0) continue;

        targets.forEach(t => t.innerHTML = "…");

        try {
            let url = `/ajax/price?id=${item.id}&mode=${item.mode}`;
            if (forceMode) url += "&force=1";

            let res = await fetch(url);
            let data = await res.json();
            let price = new Intl.NumberFormat("id-ID").format(data.price ?? 0);
            targets.forEach(t => t.innerHTML = price);
        } catch (e) {
            targets.forEach(t => t.innerHTML = "0");
        }
        await new Promise(r => setTimeout(r, 150));
    }
}

function reloadPricesAfterAjax() {
    collectLazyPrices();
    loadPrices();
}

function showFilterToast() {
    const toastEl = document.getElementById("toastFilterError");
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Initial load
document.addEventListener("DOMContentLoaded", () => {
    collectLazyPrices();
    loadPrices();
});
</script>
@endpush
@endsection