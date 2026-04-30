@extends('layouts.admin')

@section('page-title', 'Checkout')

@section('content')

<div class="container py-4">

    <div class="row g-4">

        {{-- ITEM LIST --}}
        <div class="col-lg-8">

            @foreach($order->items as $item)

                <div class="card border-0 shadow-sm rounded-4 mb-3">

                    <div class="card-body">

                        <div class="d-flex gap-3">

                            {{-- IMAGE --}}
                            <img 
                                src="{{ $item->product?->images->first()?->url }}"
                                class="rounded-3"
                                style="width:120px; height:120px; object-fit:cover;">

                            {{-- INFO --}}
                            <div class="flex-grow-1">

                                <h5 class="fw-semibold mb-1">
                                    {{ $item->item_name }}
                                </h5>

                                <small class="text-muted">
                                    {{ $item->accurate_item_no }}
                                </small>

                                <div class="mt-3">
                                    <span class="text-danger fw-bold fs-5">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </span>
                                </div>

                            </div>

                            {{-- QTY --}}
                            <div class="d-flex flex-column align-items-end">

                                <div class="text-muted small mb-2">
                                    Qty
                                </div>

                                <input 
                                    type="number"
                                    class="form-control"
                                    value="{{ $item->quantity }}"
                                    min="1"
                                    style="width:80px;"
                                    readonly>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        {{-- SUMMARY --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body">

                    <h4 class="fw-bold mb-4">
                        Checkout
                    </h4>

                    <form action="" method="POST">

                        @csrf

                        {{-- CUSTOMER --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Customer
                            </label>

                            <select 
                                name="customer_no"
                                id="customerNo"
                                class="form-select"
                                required>
                            </select>

                        </div>

                        {{-- BRANCH --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Branch
                            </label>

                            <select 
                                name="branch_name"
                                id="branchName"
                                class="form-select"
                                required>
                            </select>

                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Description
                            </label>

                            <textarea 
                                name="description"
                                class="form-control"
                                rows="3"></textarea>

                        </div>

                        {{-- TOTAL --}}
                        <div class="border-top pt-3 mb-4">

                            <div class="d-flex justify-content-between">

                                <span class="fw-semibold">
                                    Total
                                </span>

                                <span class="fw-bold text-danger fs-5">
                                    @php
                                        $total = $order->items->sum('unit_price');
                                    @endphp
                                    Rp {{ number_format($total, 0, ',', '.') }}

                                </span>

                            </div>

                        </div>

                        {{-- BUTTON --}}
                        <button class="btn btn-dark w-100 rounded-pill py-3">

                            Submit Sales Order

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection