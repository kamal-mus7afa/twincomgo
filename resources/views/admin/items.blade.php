@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Daftar Produk')

@section('content')
<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --dark: #1e293b;
        --light: #f8fafc;
    }

    /* ===== HEADER SECTION ===== */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .page-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(90deg, #fff, #e0e7ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-description {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .stat-item:nth-child(2) { border-left-color: var(--success); }
    .stat-item:nth-child(3) { border-left-color: var(--info); }
    .stat-item:nth-child(4) { border-left-color: var(--warning); }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== FILTER CARD ===== */
    .filter-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-title {
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        transform: translateY(-2px);
    }

    .input-group-text {
        border-radius: 12px 0 0 12px;
        border: 2px solid #e2e8f0;
        border-right: none;
        background: #f8fafc;
    } */

    .search-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .reset-btn {
        background: #64748b;
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }

    .reset-btn:hover {
        background: #475569;
        transform: translateY(-2px);
        color: white;
    }

    .export-btn {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        border: none;
        border-radius: 12px;
        color: white;
        padding: 12px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        color: white;
    }

    /* ===== TABLE SECTION ===== */
    .table-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .table-header {
        background: linear-gradient(135deg, #1e293b 0%, #374151 100%);
        color: white;
        padding: 20px 25px;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-header h3 {
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.5rem;
    }

    .table-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .per-page-select {
        width: 80px;
        border-radius: 10px;
        border: none;
        padding: 8px;
        font-weight: 600;
    }

    .table-container {
        max-height: 600px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--primary) #f1f5f9;
    }

    .table-container::-webkit-scrollbar {
        width: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 10px;
    }

    .table {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border: none;
        font-weight: 500;
    }

    .td-harga {
        width: 150px;
        text-align: right;
        vertical-align: middle;
    }
    .harga-grid {
        display: grid;
        grid-template-columns: 30px 1fr; /* Rp selalu 30px, nominal menyesuaikan */
        justify-content: end;
        align-items: center;
    }
    .harga-rp {
        text-align: left;
    }

    .harga-nominal {
        text-align: right;
    }

    /* ===== PRODUCT IMAGE ===== */
    .product-image-sm {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .product-image-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    /* ===== BADGES ===== */
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-available {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-lowstock {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .badge-soldout {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* ===== PRICE TAG ===== */
    .price-tag {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-block;
    }

    /* ===== PAGINATION ===== */
    .pagination-section {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 20px 25px;
        border-radius: 0 0 20px 20px;
    }

    .pagination-info {
        color: #64748b;
        font-weight: 500;
    }

    .pagination {
        margin: 0;
        gap: 5px;
    }

    .page-link {
        border-radius: 10px !important;
        border: none;
        padding: 8px 14px;
        color: var(--dark);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        color: #475569;
        margin-bottom: 0.5rem;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        z-index: 100;
        border-radius: 20px;
    }

    #item-container {
        position: relative;
        min-height: 200px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-header {
            flex-direction: column;
            text-align: center;
        }
        
        .table-header {
            flex-direction: column;
            text-align: center;
        }
        
        .table-container {
            max-height: none;
        }
        
        .table thead th,
        .table tbody td {
            padding: 12px 8px;
            font-size: 0.6rem;
        }
        
        .product-image-sm,
        .product-image-placeholder {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
        .mobile-list {
            display: none;
        }

        /* 🔹 Form filter di atas */
        .form-label,
        .form-select,
        .form-control,
        .input-group-text {
            font-size: 10px !important;
        }

        .form-select,
        .form-control {
            padding: 4px 6px;
            height: auto;
        }

        /* 🔹 Tombol pencarian kecil */
        .btn {
            font-size: 10px;
            padding: 5px 8px;
        }
    }
    @media (min-width: 768px) {
        .mobile-list {
            display: none;
        }
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER SECTION --}}
    <div class="page-header animate-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-content">
                <h1>
                    <i class="bi bi-box-seam me-3"></i>Daftar Produk
                </h1>
                <p class="page-description">Kelola dan pantau semua produk yang tersedia</p>
            </div>
        </div>
    </div>

    {{-- FILTER SECTION --}}
    <div class="bg-white rounded-4 p-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-funnel me-2"></i>Filter & Search Products
            </h5>
            
            {{-- DESKTOP EXPORT --}}
            <div class="d-none d-md-flex gap-2">
                <a href="#" id="btn-export-pdf" class="btn text-white" style="background: linear-gradient(135deg, #dc2626, #b91c1c);" data-export-url="{{ route('items.exportPdf') }}">
                    <i class="bi bi-filetype-pdf me-2"></i>Export PDF
                </a>
            </div>
        </div>
        
        @include('items.partials.filter')

        {{-- MOBILE EXPORT --}}
        <div class="d-md-none mt-3">
            <a href="#" id="btn-export-pdf-mobile" class="btn text-white w-100" style="background: linear-gradient(135deg, #dc2626, #b91c1c);" data-export-url="{{ route('items.exportPdf') }}">
                <i class="bi bi-filetype-pdf me-2"></i>Export PDF
            </a>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="table-card animate-card" style="animation-delay: 0.3s">
        <div class="table-header">
            <h3>
                <i class="bi bi-grid-3x3-gap-fill"></i>
                Products List
            </h3>
            <div class="table-controls">
                <span class="text-white-50">Items per page:</span>
                <select id="per_page" class="form-select per-page-select">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>

        {{-- PRODUCTS CONTAINER --}}
        <div id="item-container">
            @include('items.partials.item-table', ['items' => $items])
        </div>

        {{-- PAGINATION --}}
        <div id="pagination-container" class="pagination-section">
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
    <div id="toastFilterError" class="toast align-items-center text-bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Filter harga terlalu sempit.<br>Perbesar rentang harganya.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

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
                window.scrollTo({ top: 0, behavior: "smooth" });
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
        const btn = e.target.closest("#btn-export-pdf");
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