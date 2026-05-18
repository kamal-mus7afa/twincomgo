@extends('layouts.app')

@section('page-title', 'Checkout')

@section('content')


@php

$groupedItems = $order->items
    ->groupBy(function ($item) {

        return $item->secondProduct->sales_order_number;
    });

@endphp

<div class="container-fluid">

    <div class="row g-4">

        {{-- ITEM LIST --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-3">

                {{-- HEADER --}}
                <div class="card-header bg-white border-0 p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                        <div>

                            <h4 class="fw-bold mb-1">
                                <i class="bi bi-cart-check-fill text-primary me-2"></i>
                                Checkout Order
                            </h4>

                            <small class="text-muted">
                                <i class="bi bi-box-seam me-1"></i>
                                {{ $order->items->count() }} item dipilih
                            </small>

                        </div>

                        <div class="d-flex flex-wrap gap-3">

                            {{-- CUSTOMER --}}
                            <div style="min-width:250px;">

                                <label class="form-label small fw-semibold">
                                    <i class="bi bi-person-badge me-1"></i>
                                    Customer
                                </label>

                                <select
                                    name="customer_no"
                                    id="customerNo"
                                    class="form-select"
                                    required
                                ></select>

                            </div>

                            {{-- BRANCH --}}
                            <div style="min-width:220px;">

                                <label class="form-label small fw-semibold">
                                    <i class="bi bi-building me-1"></i>
                                    Cabang
                                </label>

                                <select
                                    name="branch_name"
                                    id="branchName"
                                    class="form-select"
                                    required
                                ></select>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- BODY --}}
                <div class="card-body p-4">

                    @foreach($groupedItems as $salesOrder => $items)

                        @php
                            $mainItem = $items->sortByDesc('unit_price')->first();

                            $packageTotal = $items->sum('unit_price');
                        @endphp

                        <div class="card border-0 bg-light rounded-4 mb-3 shadow-sm">

                            <div class="card-body p-3">

                                <div class="d-flex justify-content-between align-items-start">

                                    <div>

                                        {{-- MAIN PRODUCT --}}
                                        <h5 class="fw-bold mb-1">
                                            <i class="bi bi-box-seam text-primary me-1"></i>
                                            {{ $mainItem->item_name }}
                                        </h5>

                                        <small class="text-muted">
                                            {{ $salesOrder }}
                                        </small>

                                        <div class="mt-2">

                                            <small
                                                id="stock-{{ $mainItem->id }}"
                                                class="text-muted fw-semibold"
                                            >
                                                <i class="bi bi-box-seam"></i>
                                                Pilih gudang untuk melihat stock
                                            </small>

                                        </div>
                                        {{-- LIST ITEM PACKAGE --}}
                                        <div class="mt-3">

                                            @foreach($items as $subItem)

                                                <div class="border rounded-3 bg-white p-2 mb-2">

                                                    <div class="d-flex justify-content-between">

                                                        <div>

                                                            <div class="fw-semibold">
                                                                {{ $subItem->item_name }}
                                                            </div>

                                                            <small class="text-muted">
                                                                {{ $subItem->accurate_item_no }}
                                                            </small>

                                                            <div>
                                                                <span class="fw-bold text-success">
                                                                    SN : {{ $subItem->serial_number }}
                                                                </span>
                                                            </div>

                                                        </div>

                                                        <div class="text-end">

                                                            <div class="fw-bold text-danger">

                                                                Rp {{ number_format($subItem->unit_price, 0, ',', '.') }}

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>

                                    {{-- REMOVE PACKAGE --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="removeItem({{ $mainItem->id }})"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>

                                </div>

                                {{-- WAREHOUSE --}}
                                <div class="mt-3">

                                    <label class="form-label fw-semibold small">
                                        <i class="bi bi-building-fill me-1"></i>
                                        Gudang Paket
                                    </label>

                                    <select
                                        id="warehouse-{{ $mainItem->id }}"
                                        class="warehouse-select"
                                        data-package="{{ $salesOrder }}"
                                        data-item-id="{{ $mainItem->id }}"
                                        data-item-no="{{ $mainItem->accurate_item_no }}"
                                    ></select>

                                </div>

                                {{-- TOTAL PACKAGE --}}
                                <div class="mt-3 text-end">

                                    <div class="fw-bold fs-5 text-danger">

                                        Total Paket:
                                        Rp {{ number_format($packageTotal, 0, ',', '.') }}

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>

        {{-- SUMMARY --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm rounded-4 sticky-top">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-receipt me-2 text-primary"></i>
                        Ringkasan
                    </h4>

                    {{-- DESCRIPTION --}}
                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-pencil-square me-1"></i>
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control rounded-4"
                            rows="4"
                            placeholder="Tambahkan catatan..."
                        ></textarea>

                    </div>

                    {{-- TOTAL --}}
                    <div class="bg-light rounded-4 p-3 mb-4">

                        <div class="d-flex justify-content-between align-items-center">

                            <span class="fw-semibold">
                                <i class="bi bi-calculator-fill me-1"></i>
                                Total
                            </span>

                            <span class="fw-bold text-danger fs-4">

                                @php
                                    $total = $order->items->sum('unit_price');
                                @endphp

                                <i class="bi bi-currency-rupee me-1"></i>
                                Rp {{ number_format($total, 0, ',', '.') }}

                            </span>

                        </div>

                    </div>

                    {{-- BUTTON --}}
                    <button
                        type="button"
                        onclick="checkoutOrder()"
                        class="btn btn-dark w-100 rounded-pill py-3 fw-semibold"
                    >
                        <i class="bi bi-check2-circle me-2"></i>
                        Submit Sales Order
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

@push('scripts')

<script>

document
    .querySelectorAll('.warehouse-select')
    .forEach(el => {

        el.addEventListener('change', async function () {

            const warehouseName = this.value;

            const itemNo =
                this.dataset.itemNo;

            const itemId =
                this.dataset.itemId;

            const stockEl =
                document.getElementById(
                    `stock-${itemId}`
                );

            stockEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

            try {

                const res = await fetch(
                    `/item-stock?no=${itemNo}&warehouseName=${warehouseName}`
                );

                const result = await res.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                stockEl.innerHTML = `<i class="bi bi-database"></i> ${result.stock}`;

            } catch (err) {

                stockEl.innerHTML =
                    '<i class="bi bi-exclamation-triangle"></i> Error';

                console.error(err);
            }
        });
    });

let customerSelect = new TomSelect('#customerNo', {

    valueField: 'customerNo',

    labelField: 'name',

    searchField: ['name', 'mobilePhone', 'customerNo'],

    preload: true,

    placeholder: '🔍 Cari customer ...',

    load: function(query, callback) {

        fetch(`/customer?search=${query}`)

            .then(res => res.json())

            .then(data => {

                callback(data.d);

            })

            .catch(() => callback())
    },

    render: {

        option: function(item, escape) {

            return `
                <div>
                    <i class="bi bi-person-circle me-2"></i>
                    <strong>${escape(item.name)}</strong><br>
                    <small class="text-muted">
                        <i class="bi bi-upc-scan"></i> No: ${escape(item.customerNo || '-')}
                    </small><br>
                    <small class="text-muted">
                        <i class="bi bi-telephone"></i> Phone: ${escape(item.mobilePhone || '-')}
                    </small>
                </div>
            `;
        },

        item: function(item, escape) {

            return `
                <div>
                    <i class="bi bi-person-circle me-2"></i>
                    ${escape(item.name || '-')}
                </div>
            `;
        }
    }
});

let branchSelect = new TomSelect('#branchName', {

    valueField: 'name',

    labelField: 'name',

    searchField: 'name',

    preload: true,

    placeholder: "🔍 Cari/Pilih Cabang ...",

    load: function(query, callback) {

        fetch(`/branch?search=${query}`)

            .then(res => res.json())

            .then(data => {

                callback(data.d)

            })

            .catch(() => callback())
    },

    render: {

        option: function(item, escape) {

            return `
                <div>
                    <i class="bi bi-building me-2"></i>
                    <strong>${escape(item.name)}</strong>
                </div>
            `
        },

        item: function(item, escape) {

            return `
                <div>
                    <i class="bi bi-building me-2"></i>
                    ${escape(item.name || '-')}
                </div>
            `;
        }
    }
});

async function removeItem(itemId)
{
    try {
        const confirm = await Swal.fire({
            title: 'Hapus item?',
            text: 'Item akan dihapus dari keranjang',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Processing...',
            text: 'Menghapus item',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const res = await fetch(
            `/cart/item/${itemId}`,
            {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        '{{ csrf_token() }}'
                }
            }
        );

        const result = await res.json();

        if (!res.ok) {
            throw new Error(result.message);
        }

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Item dihapus',
            timer: 1200,
            showConfirmButton: false
        }).then(() => {

            if (result.empty) {

                window.location.href = '/daftar-product';

                return;
            }

            location.reload();

        });

    } catch (err) {

        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: err.message
        });
    }
}

const warehouseInstances = {};

document
    .querySelectorAll('.warehouse-select')
    .forEach(el => {

        const itemId =
            el.dataset.itemId;

        const itemNo =
            el.dataset.itemNo;

        warehouseInstances[itemId] =
            new TomSelect(el, {

                valueField: 'name',

                labelField: 'name',

                searchField: 'name',

                preload: false,

                placeholder: '🔍 Cari gudang...',

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

                        return `
                            <div>
                                <i class="bi bi-building-fill me-2"></i>
                                ${escape(item.name)}
                            </div>
                        `;
                    },

                    item: function(item, escape) {

                        return `
                            <div>
                                <i class="bi bi-building-fill me-2"></i>
                                ${escape(item.name)}
                            </div>
                        `;
                    }
                },

                onChange: async function(value) {

                    if (!value) return;

                    const stockEl =
                        document.getElementById(
                            `stock-${itemId}`
                        );

                    stockEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';

                    try {

                        const res = await fetch(
                            `/item-stock?no=${itemNo}&warehouseName=${value}`
                        );

                        const result =
                            await res.json();

                        stockEl.innerHTML = `<i class="bi bi-database"></i> ${result.stock ?? 0}`;

                    } catch (err) {

                        stockEl.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                    }
                }
            });
    });

async function checkoutOrder()
{
    try {

        // Validasi customer
        if (!customerSelect.getValue()) {
            throw new Error('Silakan pilih customer terlebih dahulu');
        }

        // Validasi branch
        if (!branchSelect.getValue()) {
            throw new Error('Silakan pilih cabang terlebih dahulu');
        }

        // Validasi warehouse untuk setiap item
        let warehouseValid = true;
        Object.values(warehouseInstances).forEach(instance => {
            if (!instance.getValue()) {
                warehouseValid = false;
            }
        });

        if (!warehouseValid) {
            throw new Error('Silakan pilih gudang untuk semua item');
        }

        Swal.fire({

            title: 'Processing...',

            text: 'Menyimpan ke Accurate',

            allowOutsideClick: false,

            didOpen: () => {

                Swal.showLoading();
            }
        });

        const res = await fetch('/checkout', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'Accept': 'application/json',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'
            },

            body: JSON.stringify({

                customer_no:
                    customerSelect.getValue(),

                branch_name:
                    branchSelect.getValue(),

                items: Object.values(warehouseInstances)
                    .map(instance => {

                        const el = instance.input;

                        return {

                            order_item_id:
                                el.dataset.itemId,

                            warehouse_name:
                                instance.getValue()
                        };
                    }),

                description:
                    document.querySelector(
                        'textarea[name="description"]'
                    ).value
            })
        });

        const result = await res.json();

        if (!res.ok) {

            throw new Error(result.message);
        }

        Swal.fire({

            icon: 'success',

            title: 'Berhasil!',

            text: 'Sales Order berhasil dibuat',

            showConfirmButton: true,
            
            confirmButtonText: '<i class="bi bi-check-lg"></i> OK'

        }).then(() => {

            window.location.href = '/daftar-product';

        });

    } catch (error) {

        Swal.fire({

            icon: 'error',

            title: 'Gagal',

            text: error.message
        });
    }
}

</script>

@endpush

@endsection