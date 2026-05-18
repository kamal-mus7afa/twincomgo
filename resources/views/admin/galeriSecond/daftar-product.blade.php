@extends('layouts.app')

@section('page-title', 'Galeri Second')

@section('content')

<div class="container-fluid py-4 px-3">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-white mb-0">Barang Second</h4>
            <p class="text-white small mb-0">Pilih barang second berkualitas dengan harga terbaik</p>
        </div>

        @php

        $cartCount =
            $draftOrder?->items
                ?->groupBy(
                    fn($item) =>
                        $item->secondProduct->sales_order_number
                )
                ->count() ?? 0;

        @endphp

        <a href="{{ route('cart.index') }}"
        class="text-white position-relative text-decoration-none">

            <i class="bi bi-cart3 fs-3"></i>

            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger px-2 py-1 small">

                {{ $cartCount }}

            </span>

        </a>
    </div>

    <!-- PRODUCT GRID -->
    <div class="row g-3">
        @foreach ($seconds as $second)
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 product-card">

                <!-- IMAGE -->
                <div class="position-relative overflow-hidden rounded-top-4" style="background: #f8f9fa;">
                    <div id="product-gallery">

                        <div id="carousel-{{ $second->id }}"
                            class="carousel slide"
                            data-bs-ride="carousel">

                            <div class="carousel-inner">

                                @forelse ($second->images as $key => $img)

                                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">

                                        <a 
                                            href="{{ $img->url }}"
                                            data-pswp-width="{{ $img->width ?? 1200 }}"
                                            data-pswp-height="{{ $img->height ?? 1200 }}"
                                        >
                                            <img 
                                                src="{{ $img->url }}"
                                                class="d-block w-100 product-img"
                                                style="height:180px;object-fit:cover;cursor:pointer;"
                                            >
                                        </a>

                                    </div>

                                @empty

                                    <div class="carousel-item active">

                                        <a 
                                            href="{{ asset('images/noimage.jpg') }}"
                                            data-pswp-width="1200"
                                            data-pswp-height="1200"
                                        >
                                            <img 
                                                src="{{ asset('images/noimage.jpg') }}"
                                                class="d-block w-100 product-img"
                                                style="height:180px;object-fit:cover;cursor:pointer;"
                                            >
                                        </a>

                                    </div>

                                @endforelse

                            </div>

                        </div>

                    </div>

                    <!-- BADGE SECOND -->
                    <span class="badge bg-dark position-absolute top-0 start-0 m-2 px-2 py-1 rounded-pill small">
                        Second
                    </span>
                </div>

                <!-- BODY -->
                <div class="card-body p-3 d-flex flex-column">

                    <!-- NAMA & ITEM NO -->
                    <div class="title-wrapper">
                        <h6 
                            class="fw-semibold text-dark mb-1 item-title"
                            title="{{ $second->item_name }}"
                        >
                            {{ $second->item_name }}
                        </h6>
                    </div>

                    <!-- SERIAL NUMBER -->
                    <div class="mb-2">
                        <code class="small bg-light px-2 py-1 rounded text-secondary">SN: {{ $second->serial_number ?? '-' }}</code>
                    </div>

                    <!-- GARANSI: TYPE + MASA BERLAKU -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <i class="bi bi-shield-check text-success" style="font-size: 12px;"></i>
                            <span class="small fw-semibold text-success">{{ strtoupper($second->type_garansi ?? 'Tanpa Garansi') }}</span>
                        </div>
                        @if($second->tanggal_fake)
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar3"></i> Berlaku s.d: 
                            {{ \Carbon\Carbon::parse($second->tanggal_fake)->isoFormat('D MMM YYYY') }}
                        </small>
                        @else
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar3"></i> Masa berlaku: -
                        </small>
                        @endif
                    </div>

                    <!-- HARGA -->
                    <div class="mt-auto">
                        <div class="fw-bold text-danger fs-5 mb-2">
                            Rp {{ number_format($second->selling_price, 0, ',', '.') }}
                        </div>

                        <button onclick="keepBarang({{ $second->id }})" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-semibold">
                            🛒 + Keranjang
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

@push('styles')
<style>
    .product-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.1) !important;
    }
    .product-img {
        transition: transform 0.3s ease;
    }
    .product-card:hover .product-img {
        transform: scale(1.03);
    }

    .title-wrapper{
        width: 100%;
    }
    
    .item-title{
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    @keyframes marquee{
        0%{
            transform: translateX(0);
        }

        100%{
            transform: translateX(-100%);
        }
    }
</style>
@endpush

@push('scripts')
<script>
async function keepBarang(id) {
    try {
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const res = await fetch(`/booked/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await res.json();
        if (!res.ok) throw new Error(result.message);

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Produk masuk ke keranjang',
            timer: 1500,
            showConfirmButton: false
        }).then(() => location.reload());

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: error.message
        });
    }
}
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
@endpush

@endsection