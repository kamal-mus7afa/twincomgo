@extends('layouts.app')

@section('content')

{{-- ============================
    STYLE
============================ --}}
@include('items.karyawan.css')

<div class="container-fluid">

    {{-- ============================
        HEADER
    ============================ --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="fw-bold"><i class="bi bi-box"></i> Detail Produk</h3>

        <div>
            <button class="btn btn-light fw-semibold me-2" onclick="history.back()">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </button>

            <a href="#" id="btn-export-pdf" class="btn btn-danger fw-semibold">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    {{-- ============================
        FILTERS
    ============================ --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-3">

            {{-- Cabang --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Harga Cabang</label>

                <div class="position-relative">
                    <select id="branchSelect" class="form-select pe-5">
                        <option value="">Semua Cabang</option>
                    </select>

                    <div id="priceSpinner"
                        class="spinner-border spinner-border-sm text-success position-absolute d-none"
                        style="top:30%; right:40px; width:16px; height:16px;">
                    </div>
                </div>
            </div>

            {{-- Jenis Harga --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Tampilkan Harga</label>
                <select id="priceType" class="form-select">
                    <option value="all" selected>Semua Harga</option>
                    <option value="user">User</option>
                    <option value="reseller">Reseller</option>
                    <option value="partner">Twincom Partner</option>
                </select>
            </div>

            {{-- Lokasi Gudang --}}
            <div class="col-md-4">
                <label class="fw-semibold mb-1">Filter Gudang</label>
                <select id="warehouseFilter" class="form-select" multiple>
                    <option value="store">Store</option>
                    <option value="tsc">TSC</option>
                    <option value="reseller">Reseller</option>
                    <option value="konsinyasi">Konsinyasi</option>
                    <option value="panda">Panda</option>
                    <option value="transit">Transit</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ============================
        IMAGE + PRICE
    ============================ --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-4 align-items-center">

            {{-- IMAGE --}}
            <div class="col-md-4 text-center">
                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel" >
                    <div class="carousel-inner">
                        @forelse ($images as $index => $file)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img 
                                    src="{{ route('proxy.image', ['file' => $file, 'session' => $session]) }}"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                    onerror="this.onerror=null; this.src='{{ asset('images/noimage.jpg') }}';"
                                >
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <img 
                                    src="{{ asset('images/noimage.jpg') }}" 
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                >
                            </div>
                        @endforelse
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>

            {{-- DETAIL & PRICE --}}
            <div class="col-md-8">

                <div class="row g-2 align-items-center mb-3">
                    <!-- Untuk mobile: full width, untuk desktop: 10 kolom -->
                    <div class="col-12 col-md-10">
                        <h5 class="fw-bold text-primary mb-0" style="cursor: pointer" onclick="copyText('{{$item['name']}}')">{{ $item['name'] }}</h5>
                    </div>
                    
                    <!-- Untuk mobile: full width dengan text alignment, untuk desktop: 2 kolom -->
                    <div class="col-12 col-md-2 text-md-end mt-2 mt-md-0 text-end">
                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3 fs-6 d-inline-block">
                            <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4" id="userPriceBox">
                        <div class="price-box p-3 h-100">
                            <div class="title text-success mb-2">
                                <i class="bi bi-person-circle me-1"></i> User
                            </div>
                            
                            {{-- HARGA --}}
                            <p class="text-muted mb-1 small">Harga</p>
                            @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                                @foreach($unitPrices as $unitName => $p)
                                    @if(isset($p['user']) && $p['user'] > 0)
                                        <h3 id="userPrice" class="price-user" data-unit="{{$unitName}}" style="cursor: pointer" onclick="copyText('{{ number_format($p['user'], 0, ',', '.') }}')">
                                            Rp {{ number_format($p['user'], 0, ',', '.') }} <small>/ {{ strtoupper($unitName) }}</small>
                                        </h3>
                                    @endif
                                @endforeach
                            @else
                                <h3 id="userPriceMain" class="price-user" style="cursor: pointer" onclick="copyText('{{ number_format($prices['user'], 0, ',', '.') }}')">
                                    Rp {{ number_format($prices['user'],0,',','.') }}
                                </h3>
                            @endif
                            
                            <hr class="my-2">
                            
                            {{-- GARANSI --}}
                            <div class="mb-2">
                                <p class="text-muted mb-1 small">Garansi</p>
                                <p class="fw-semibold mb-0">{{ $item['charField6'] ?? '-' }}</p>
                            </div>

                            @php
                                $kelengkapan = array_unique(array_filter([
                                    trim($item['charField8'] ?? ''),
                                    trim($item['charField9'] ?? ''),
                                ]));
                            @endphp
                            
                            {{-- KELENGKAPAN --}}
                            <div class="kelengkapan-section">
                                <p class="text-muted mb-1 small">Kelengkapan</p>
                                <div class="kelengkapan-content">
                                    @if (empty($kelengkapan))
                                        <p class="fw-semibold mb-0 small">-</p>
                                    @else
                                        @foreach ($kelengkapan as $k)
                                            <p class="fw-semibold mb-0 small">{{ $k }}</p>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" id="partnerPriceBox">
                        <div class="price-box p-3 h-100">
                            <div class="title-partner mb-2">
                                <i class="bi bi-person-vcard"></i> Twincom Partner
                            </div>
                            
                            {{-- HARGA --}}
                            <p class="text-muted mb-1 small">Harga</p>
                                <h3 id="userPriceMain" class="price-partner" style="cursor: pointer" onclick="copyText('{{ number_format($partnerPrice,0,',','.') }}')">
                                    Rp {{ number_format($partnerPrice,0,',','.') }}
                                </h3>
                            
                            <hr class="my-2">
                            
                            {{-- GARANSI --}}
                            <div class="mb-2">
                                <p class="text-muted mb-1 small">Garansi</p>
                                <p class="fw-semibold mb-0">{{ $item['charField6'] ?? '-' }}</p>
                            </div>

                            @php
                                $kelengkapan = array_unique(array_filter([
                                    trim($item['charField8'] ?? ''),
                                    trim($item['charField9'] ?? ''),
                                ]));
                            @endphp
                            
                            {{-- KELENGKAPAN --}}
                            <div class="kelengkapan-section">
                                <p class="text-muted mb-1 small">Kelengkapan</p>
                                <div class="kelengkapan-content">
                                    @if (empty($kelengkapan))
                                        <p class="fw-semibold mb-0 small">-</p>
                                    @else
                                        @foreach ($kelengkapan as $k)
                                            <p class="fw-semibold mb-0 small">{{ $k }}</p>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" id="resellerPriceBox">
                        <div class="price-box p-3 h-100">
                            <div class="title text-primary mb-2">
                                <i class="bi bi-people-fill me-1"></i> Reseller
                            </div>
                            
                            {{-- HARGA --}}
                            <p class="text-muted mb-1 small">Harga</p>
                            @if (!empty($hasMultiUnitPrices) && $hasMultiUnitPrices)
                                @foreach($unitPrices as $unitName => $p)
                                    @if(isset($p['reseller']) && $p['reseller'] > 0)
                                        <h3 id="resellerPrice" class="price-reseller" data-unit="{{$unitName}}" style="cursor: pointer" onclick="copyText('{{ number_format($p['reseller'],0,',','.') }}')">
                                            Rp {{ number_format($p['reseller'],0,',','.') }} <small>/ {{ strtoupper($unitName) }}</small>
                                        </h3>
                                    @endif
                                @endforeach
                            @else
                                <h3 id="resellerPriceMain" class="price-reseller" style="cursor: pointer" onclick="copyText('{{ number_format($prices['reseller'],0,',','.') }}')">
                                    Rp {{ number_format($prices['reseller'],0,',','.') }}
                                </h3>
                            @endif
                            
                            <hr class="my-2">
                            
                            {{-- GARANSI --}}
                            <div class="mb-2">
                                <p class="text-muted mb-1 small">Garansi</p>
                                <p class="fw-semibold mb-0">{{ $item['charField7'] ?? '-' }}</p>
                            </div>
                            
                            {{-- KELENGKAPAN --}}
                            <div class="kelengkapan-section">
                                <p class="text-muted mb-1 small">Kelengkapan</p>
                                <div class="kelengkapan-content">
                                    @if(isset($item['charField8']) && trim($item['charField8']) !== '')
                                        <p class="fw-semibold mb-1 small">{{ $item['charField8'] }}</p>
                                    @endif
                                    @if(!isset($item['charField8']) || trim($item['charField8']) === '')                                        
                                        <p class="fw-semibold mb-0 small">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
        @if(trim($note) !== '')
        <div class="card p-4 mb-2 mt-3 shadow-sm" id="catatanCard">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-journal-text me-1"></i> Selling Point
            </h5>
            
            <div class="text-secondary" style="font-size: 16px;">
                {!! nl2br(e($note)) !!}
            </div>
        </div>
        @endif
    </div>
    @if($snList->isNotEmpty())
        <div class="mt-4">
            <h5 class="mb-3">Gambar barang perSN</h5>

            <div class="row">
                @foreach ($snList as $sn)
                    <div class="col-md-4 col-lg-3 mb-4">
                        {{-- Container dengan posisi relative untuk tag yang menjorok --}}
                        <div class="position-relative">
                            
                            {{-- TAG READY di pojok KANAN luar card (nempel/menjorok) --}}
                            @if($sn->status === 'unkeep')
                                <div class="position-absolute top-0 end-0 z-3" style="transform: translate(10%, -30%);">
                                    <span class="badge bg-success px-3 py-2 rounded-pill shadow-lg fs-6 fw-bold">
                                        ✅ Tersedia
                                    </span>
                                </div>
                            @endif

                            {{-- CARD --}}
                            <div class="card h-100 shadow-sm rounded-3 overflow-hidden">
                                @if($sn->images && $sn->images->count())
                                    <div id="carousel-{{ $sn->id }}" class="carousel slide" data-bs-ride="carousel">
                                        
                                        <div class="carousel-inner">
                                            @foreach($sn->images as $key => $img)
                                                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                                    <img src="{{ $img->url }}" 
                                                        class="d-block w-100"
                                                        style="height:180px; object-fit:cover;">
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- tombol kiri --}}
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $sn->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </button>

                                        {{-- tombol kanan --}}
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $sn->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </button>

                                    </div>
                                @else
                                    <img src="{{ asset('images/noimage.jpg') }}" 
                                        class="card-img-top"
                                        style="height:180px; object-fit:cover;">
                                @endif
                                <div class="card-body bg-light">
                                    {{-- SN --}}
                                    <div class="mb-3 pb-2 border-bottom">
                                        <div class="text-muted small text-uppercase mb-1">Serial Number</div>
                                        <div class="fw-bold fs-6 text-dark">{{ $sn->sn }}</div>
                                    </div>

                                    {{-- Warehouse --}}
                                    <div class="mb-3 pb-2 border-bottom">
                                        <div class="text-muted small text-uppercase mb-1">Lokasi Warehouse</div>
                                        <div>
                                            <span class="fw-semibold">{{ $sn->warehouse }}</span>
                                        </div>
                                    </div>

                                    {{-- Garansi (Tipe + Masa) --}}
                                    <div class="mt-2">
                                        <div class="text-muted small text-uppercase mb-1">Garansi</div>
                                        <div>
                                            @if ($sn->type_garansi !== null)
                                                <span class="badge bg-info me-2">{{ ucfirst( $sn->type_garansi)}}</span>
                                            @else
                                                <span class="badge bg-warning me-2">Tidak ada</span>
                                            @endif
                                            {{-- <span class="badge bg-info me-2">{{ $sn->type_garansi ?? 'Tidak ada' }}</span> --}}
                                            <span class="small text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ $sn->tanggal_fake ? \Carbon\Carbon::parse($sn->tanggal_fake)->format('d/m/Y') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============================
    WAREHOUSE GROUP TABLES
    ============================ --}}
    @foreach([
        'store' => 'Store',
        'tsc' => 'TSC',
        'reseller' => 'Reseller',
        'konsinyasi' => 'Konsinyasi',
        'panda' => 'Panda',
    ] as $key => $label)

        @php
            $var = 'warehouses' . ucfirst($key);
        @endphp

        @if(isset($$var) && count($$var) > 0)

            <div class="warehouse-card card warehouse-{{ $key }}" id="warehouse_{{ $key }}">
                <div class="card-header fw-bold">{{ $label }}</div>

                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satuan</th>
                        </tr>
                        </thead>

                        <tbody>
                            @include("partials.table" . ucfirst($key), [ $var => $$var ])
                        </tbody>
                    </table>
                </div>

                {{-- ===== TOTAL DI BAWAH CARD ===== --}}
                <div class="px-3 py-2 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>

                    <span class="total-badge" id="total_{{ $key }}">
                        {{ number_format($$var->sum('balance'), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endif
    @endforeach

    {{-- ============================
    TRANSIT (SPECIAL)
    ============================ --}}
    @if(!empty($warehousesTransit) && count($warehousesTransit) > 0)

    <div class="warehouse-card card  warehouse-transit" id="warehouse_transit">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center">

        {{-- KIRI --}}
        <div class="d-flex align-items-center gap-2 text-start">
            <span>Transit (AOL System)</span>
            <span class="badge bg-secondary">transit</span>
        </div>

        {{-- KANAN --}}
        <span class="total-badge" id="total_transit">
            {{ number_format($warehousesTransit->sum('balance'), 0, ',', '.') }}
        </span>

    </div>
</div>

    @endif
</div>


{{-- ============================
    SCRIPT SECTION
============================ --}}
@include('items.karyawan.partial-js')

@endsection
