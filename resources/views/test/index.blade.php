@extends('layouts.app')

@section('page-title', 'Activity Logs')

@section('content')

<div class="corporate-container">
    {{-- Page Header --}}
    <div class="corporate-page-header">
        <div class="corporate-header-content">
            <div class="corporate-title-section">
                <div class="corporate-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12 16H12.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <h1 class="corporate-title">Activity Logs</h1>
                    <p class="corporate-subtitle">Manage and monitor item price activities</p>
                </div>
            </div>
            <div class="corporate-stats-badge">
                <span class="corporate-stats-label">Total Items</span>
                <span class="corporate-stats-value" id="item-count-corp">0</span>
            </div>
        </div>
    </div>

    {{-- Action Panel --}}
    <div class="corporate-action-panel">
        <div class="corporate-search-section">
            <label class="corporate-label">Search Item</label>
            <div class="corporate-select-wrapper">
                <svg class="corporate-select-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M19 19L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <select id="item_select" class="corporate-select"></select>
            </div>
        </div>
        <div class="corporate-action-buttons">
            <button onclick="updatePrices()" class="corporate-btn corporate-btn-primary">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M16 2L18 4L10 12H8V10L16 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M4 4H2V18H16V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M12 4H18V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Update Prices</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="corporate-table-container">
        <div class="corporate-table-header">
            <div class="corporate-table-title">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M3 5H17M3 10H17M3 15H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <rect x="1" y="1" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span>Selected Items</span>
            </div>
            <div class="corporate-table-filters">
                <span class="corporate-record-count" id="record-count">0 records</span>
            </div>
        </div>
        
        <div class="corporate-table-wrapper">
            <table class="corporate-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Item Number</th>
                        <th>Unit Price</th>
                        <th class="corporate-action-col">Action</th>
                    </tr>
                </thead>
                <tbody id="table-body"></tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div id="empty-state-corp" class="corporate-empty-state" style="display: none;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                <path d="M3 6H21M3 12H21M3 18H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M8 3V21M16 3V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <h4>No items selected</h4>
            <p>Search and select items from the dropdown above to get started</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let selectedItems = [];
    let csrf = document.querySelector('meta[name="csrf-token"]');

    // 🔥 LOAD DATA DARI DB SAAT AWAL
    function loadFromDB() {
        fetch('/draft/list')
            .then(res => res.json())
            .then(items => {
                selectedItems = items.map(item => ({
                    no: item.item_no,
                    name: item.item_name,
                    price: item.price,
                    category: item.category || 'Uncategorized',
                    categoryId: item.category_id || '-'
                }));

                renderTable();
                updateCorporateCounters();
            })
            .catch(err => console.error('Error loading data:', err));
    }

    let ts = new TomSelect("#item_select", {
        valueField: "no",
        labelField: "text",
        searchField: "text",
        placeholder: "Search by name or item number...",
        loadThrottle: 300,
        loadingClass: 'loading',
        options: [],
        render: {
            option: function(data, escape) {
                return `<div class="corporate-option">
                            <div class="corporate-option-title">${escape(data.name)}</div>
                            <div class="corporate-option-subtitle">${escape(data.no)} • ${escape(data.category)}</div>
                        </div>`;
            },
            item: function(data, escape) {
                return `<div class="corporate-selected-item">${escape(data.name)} <span class="corporate-selected-code">${escape(data.no)}</span></div>`;
            },
            no_results: function() {
                return `<div class="corporate-no-results">No items found</div>`;
            }
        },

        load: function(query, callback) {
            if (!query.length) return callback();

            fetch(`/admin/galeri/list?search=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(items => {
                    callback(items.map(item => ({
                        no: item.no,
                        text: `${item.name} - ${item.no}`,
                        name: item.name,
                        category: item.itemCategory?.name || 'Uncategorized',
                        categoryId: item.itemCategory?.id || '-',
                    })))
                }).catch(() => callback());
        },

        onItemAdd: function(value) {
            let data = this.options[value];
            if (!data) return;

            if (selectedItems.find(i => i.no === data.no)) {
                this.clear();
                return;
            }

            fetch(`/admin/galeri/price?no=${data.no}`)
                .then(res => res.json())
                .then(priceRes => {
                    let price = priceRes.unitPrice ?? 0;

                    let item = {
                        no: data.no,
                        name: data.name,
                        price: price,
                        category: data.category,
                        categoryId: data.categoryId,
                    };

                    selectedItems.push(item);

                    // 🔥 SIMPAN KE DB
                    fetch('/draft/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf ? csrf.content : ''
                        },
                        body: JSON.stringify(item)
                    }).catch(err => console.error('Error saving:', err));

                    renderTable();
                    updateCorporateCounters();
                })
                .catch(err => console.error('Error fetching price:', err));

            this.clear();
        }
    });

    function updateCorporateCounters() {
        const totalItems = selectedItems.length;
        const countSpan = document.getElementById('item-count-corp');
        const recordSpan = document.getElementById('record-count');
        
        if (countSpan) countSpan.textContent = totalItems;
        if (recordSpan) recordSpan.textContent = `${totalItems} record${totalItems !== 1 ? 's' : ''}`;
    }

    function renderTable() {
        let html = '';
        
        if (selectedItems.length === 0) {
            document.querySelector("#table-body").innerHTML = '';
            document.getElementById('empty-state-corp').style.display = 'flex';
            return;
        }
        
        document.getElementById('empty-state-corp').style.display = 'none';

        // 🔥 grouping by category
        let grouped = {};

        selectedItems.forEach(item => {
            let categoryKey = item.category || 'Uncategorized';
            if (!grouped[categoryKey]) {
                grouped[categoryKey] = [];
            }
            grouped[categoryKey].push(item);
        });

        // 🔥 render per kategori dengan corporate styling
        for (let category in grouped) {
            html += `
                <tr class="corporate-category-row">
                    <td colspan="4">
                        <div class="corporate-category-header">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M2 2H7L9 4H16V14H2V2Z" stroke="currentColor" stroke-width="1.2" fill="none"/>
                            </svg>
                            <span>${escapeHtml(category)}</span>
                            <span class="corporate-category-count">${grouped[category].length}</span>
                        </div>
                    </td>
                </tr>
            `;

            grouped[category].forEach((item) => {
                let globalIndex = selectedItems.findIndex(i => i.no === item.no);
                html += `
                    <tr class="corporate-data-row">
                        <td class="corporate-item-name">
                            <div class="corporate-name-cell">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M5 8H11M8 5V11" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                                <span>${escapeHtml(item.name)}</span>
                            </div>
                        </td>
                        <td class="corporate-item-code">
                            <code>${escapeHtml(item.no)}</code>
                        </td>
                        <td class="corporate-item-price">
                            <span class="corporate-price">${formatRupiah(item.price)}</span>
                        </td>
                        <td class="corporate-action-cell">
                            <button onclick="removeItem(${globalIndex}, '${escapeHtml(item.no)}')" 
                                    class="corporate-btn-icon corporate-btn-danger">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M3 5H15M7 8V12M11 8V12M5 5L6 15H12L13 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <path d="M7 2H11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                                <span>Remove</span>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        document.querySelector("#table-body").innerHTML = html;
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    window.removeItem = function(index, no) {
        if (index !== -1 && index < selectedItems.length) {
            selectedItems.splice(index, 1);
        } else {
            let itemIndex = selectedItems.findIndex(i => i.no === no);
            if (itemIndex !== -1) {
                selectedItems.splice(itemIndex, 1);
            }
        }

        fetch(`/draft/delete/${encodeURIComponent(no)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf ? csrf.content : ''
            }
        }).catch(err => console.error('Error deleting:', err));

        renderTable();
        updateCorporateCounters();
    }

    function formatRupiah(angka) {
        if (angka === undefined || angka === null) angka = 0;
        return 'Rp ' + angka.toLocaleString('id-ID');
    }

    loadFromDB();

    window.updatePrices = function () {
        const btn = document.querySelector('.corporate-btn-primary');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="corporate-spinner"></span><span>Updating...</span>';
        }
        
        fetch('/draft/update-prices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(() => {
            loadFromDB();
        })
        .catch(err => console.error(err))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M16 2L18 4L10 12H8V10L16 2Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M4 4H2V18H16V16" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M12 4H18V10" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <span>Update Prices</span>
                `;
            }
        });
    }

});
</script>

<style>
/* Corporate Design System */
:root {
    --corporate-primary: #0052cc;
    --corporate-primary-dark: #0047b3;
    --corporate-secondary: #172b4d;
    --corporate-text: #172b4d;
    --corporate-text-light: #5e6c84;
    --corporate-border: #dfe4e9;
    --corporate-bg: #f4f5f7;
    --corporate-white: #ffffff;
    --corporate-danger: #de350b;
    --corporate-danger-hover: #bf2600;
    --corporate-success: #00875a;
    --corporate-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.03);
    --corporate-shadow-lg: 0 8px 16px -6px rgba(0, 0, 0, 0.08);
}

.corporate-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--corporate-bg);
    min-height: 100vh;
}

/* Page Header */
.corporate-page-header {
    margin-bottom: 2rem;
}

.corporate-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
}

.corporate-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.corporate-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--corporate-primary) 0%, var(--corporate-primary-dark) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.corporate-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--corporate-secondary);
    margin: 0 0 0.25rem 0;
    letter-spacing: -0.02em;
}

.corporate-subtitle {
    font-size: 0.875rem;
    color: var(--corporate-text-light);
    margin: 0;
}

.corporate-stats-badge {
    background: var(--corporate-white);
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: var(--corporate-shadow);
    border: 1px solid var(--corporate-border);
}

.corporate-stats-label {
    display: block;
    font-size: 0.75rem;
    color: var(--corporate-text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.corporate-stats-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--corporate-primary);
}

/* Action Panel */
.corporate-action-panel {
    background: var(--corporate-white);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1.5rem;
    flex-wrap: wrap;
    box-shadow: var(--corporate-shadow);
    border: 1px solid var(--corporate-border);
}

.corporate-search-section {
    flex: 1;
    min-width: 250px;
}

.corporate-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--corporate-text-light);
    margin-bottom: 0.5rem;
}

.corporate-select-wrapper {
    position: relative;
}

.corporate-select-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--corporate-text-light);
    pointer-events: none;
}

.corporate-select {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1px solid var(--corporate-border);
    border-radius: 10px;
    font-size: 0.875rem;
    background: var(--corporate-white);
    transition: all 0.2s;
}

.corporate-select:focus {
    outline: none;
    border-color: var(--corporate-primary);
    box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1);
}

.corporate-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    font-family: inherit;
}

.corporate-btn-primary {
    background: var(--corporate-primary);
    color: white;
}

.corporate-btn-primary:hover:not(:disabled) {
    background: var(--corporate-primary-dark);
    transform: translateY(-1px);
    box-shadow: var(--corporate-shadow-lg);
}

.corporate-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Table Container */
.corporate-table-container {
    background: var(--corporate-white);
    border-radius: 16px;
    box-shadow: var(--corporate-shadow);
    border: 1px solid var(--corporate-border);
    overflow: hidden;
}

.corporate-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--corporate-border);
    background: var(--corporate-white);
}

.corporate-table-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--corporate-secondary);
}

.corporate-record-count {
    font-size: 0.75rem;
    color: var(--corporate-text-light);
}

.corporate-table-wrapper {
    overflow-x: auto;
}

.corporate-table {
    width: 100%;
    border-collapse: collapse;
}

.corporate-table thead tr {
    background: #fafbfc;
    border-bottom: 1px solid var(--corporate-border);
}

.corporate-table thead th {
    text-align: left;
    padding: 1rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--corporate-text-light);
}

.corporate-action-col {
    text-align: center;
    width: 100px;
}

/* Category Row */
.corporate-category-row td {
    background: #fafbfc;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--corporate-border);
    border-bottom: 1px solid var(--corporate-border);
}

.corporate-category-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--corporate-secondary);
    font-size: 0.875rem;
}

.corporate-category-count {
    background: #e9ecef;
    padding: 0.125rem 0.5rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--corporate-text-light);
}

/* Data Row */
.corporate-data-row {
    border-bottom: 1px solid #f0f2f4;
}

.corporate-data-row:hover {
    background: #fafbfc;
}

.corporate-data-row td {
    padding: 1rem;
    vertical-align: middle;
}

.corporate-item-name .corporate-name-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--corporate-secondary);
    font-weight: 500;
    font-size: 0.875rem;
}

.corporate-item-name svg {
    color: var(--corporate-text-light);
    flex-shrink: 0;
}

.corporate-item-code code {
    background: #f4f5f7;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-family: 'SF Mono', monospace;
    color: var(--corporate-secondary);
}

.corporate-price {
    font-weight: 600;
    color: var(--corporate-success);
    font-size: 0.875rem;
}

/* Action Buttons */
.corporate-action-cell {
    text-align: center;
}

.corporate-btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
    background: transparent;
    font-family: inherit;
}

.corporate-btn-danger {
    color: var(--corporate-danger);
    border-color: #ffe6e0;
}

.corporate-btn-danger:hover {
    background: #fff2ef;
    border-color: var(--corporate-danger);
}

/* Empty State */
.corporate-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--corporate-text-light);
}

.corporate-empty-state svg {
    color: var(--corporate-border);
    margin-bottom: 1rem;
}

.corporate-empty-state h4 {
    font-size: 1rem;
    font-weight: 500;
    margin: 0 0 0.5rem 0;
    color: var(--corporate-secondary);
}

.corporate-empty-state p {
    font-size: 0.875rem;
    margin: 0;
}

/* TomSelect Corporate Override */
.ts-control {
    border: 1px solid var(--corporate-border) !important;
    border-radius: 10px !important;
    padding: 0.625rem 1rem 0.625rem 2.5rem !important;
    font-size: 0.875rem !important;
    font-family: inherit !important;
    box-shadow: none !important;
}

.ts-control:focus {
    border-color: var(--corporate-primary) !important;
    box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1) !important;
}

.ts-dropdown {
    border-radius: 10px !important;
    border: 1px solid var(--corporate-border) !important;
    box-shadow: var(--corporate-shadow-lg) !important;
    font-family: inherit !important;
}

.corporate-option {
    padding: 0.5rem 0;
}

.corporate-option-title {
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--corporate-secondary);
}

.corporate-option-subtitle {
    font-size: 0.75rem;
    color: var(--corporate-text-light);
    margin-top: 0.125rem;
}

.corporate-selected-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.corporate-selected-code {
    font-size: 0.75rem;
    color: var(--corporate-text-light);
}

.corporate-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .corporate-container {
        padding: 1rem;
    }
    
    .corporate-header-content {
        flex-direction: column;
    }
    
    .corporate-action-panel {
        flex-direction: column;
        align-items: stretch;
    }
    
    .corporate-btn-primary {
        justify-content: center;
    }
    
    .corporate-table thead th,
    .corporate-data-row td {
        padding: 0.75rem;
    }
    
    .corporate-btn-icon span {
        display: none;
    }
}
</style>
@endpush