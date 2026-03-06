@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- HERO BANNER / IKLAN UTAMA --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="rounded-4 overflow-hidden shadow-sm" style="height:320px; background:#e9ecef;">
                {{-- Tempat foto iklan / carousel --}}
                <img src="{{ asset('images/banner-main.jpg') }}" class="w-100 h-100" style="object-fit:cover">
            </div>
        </div>
    </div>

    {{-- PROMO PER KATEGORI --}}
    @foreach($promoByCategory as $category => $items)
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Promo {{ $category }}</h5>
            <a href="{{ route('katalog.items', ['category' => $category]) }}" class="small text-decoration-none">Lihat semua</a>
        </div>

        <div class="row g-3">
            @foreach($items as $item)
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card h-100 border-0 shadow-sm rounded-3 katalog-card"
                     onclick="window.location='{{ route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]) }}'"
                     style="cursor:pointer">

                    <div class="ratio ratio-4x3">
                        <img src="{{ $item['image'] ? route('proxy.image',['file'=>$item['image'],'session'=>$session]) : asset('images/noimage.jpg') }}"
                             class="img-fluid" style="object-fit:contain" loading="lazy">
                    </div>

                    <div class="card-body p-2">
                        <div class="fw-semibold" style="font-size:.85rem;line-height:1.2">{{ $item['name'] }}</div>
                        <div class="text-primary fw-bold mt-1" style="font-size:.9rem">Rp <span data-lazy-price data-id="{{ $item['id'] }}"></span></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- IKLAN TENGAH --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="rounded-4 overflow-hidden shadow-sm" style="height:180px; background:#dee2e6;">
                <img src="{{ asset('images/banner-middle.jpg') }}" class="w-100 h-100" style="object-fit:cover">
            </div>
        </div>
    </div>

    {{-- CTA KATALOG --}}
    <div class="text-center py-4">
        <a href="{{ route('katalog.items') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Masuk ke Katalog Lengkap</a>
    </div>

</div>
@endsection