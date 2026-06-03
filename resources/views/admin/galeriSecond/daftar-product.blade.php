@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('page-title', 'Galeri Second')

@section('content')

@push('styles')
<style>
    /* ===== PREMIUM BRAND SYSTEM ===== */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.06);
        --glass-border: rgba(255, 255, 255, 0.08);
        --card-surface: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --danger-soft: #fef2f2;
        --danger-text: #ef4444;
    }

    /* ===== HERO & STATS BANNER ===== */
    .gallery-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%);
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 32px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.3);
    }
    
    .cart-glass-btn {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 12px 20px;
        border-radius: 16px;
        transition: all 0.2s ease;
    }
    .cart-glass-btn:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
    }

    /* ===== PRODUCT RETAIL CARD ===== */
    .modern-product-card {
        background: var(--card-surface);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .modern-product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }

    /* Image Wrapper System */
    .image-container-ratio {
        position: relative;
        padding-top: 100%; /* 1:1 Aspect Ratio Perfect Square */
        background: #f8fafc;
        overflow: hidden;
    }
    .abs-carousel-wrapper {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
    }
    .modern-product-card .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .modern-product-card:hover .product-img {
        transform: scale(1.04);
    }

    /* Badges & Micro-Copy */
    .condition-badge {
        position: absolute;
        top: 14px; left: 14px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 6px 12px;
        border-radius: 30px;
        z-index: 3;
    }
    .sn-code {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 11px;
        background: #f1f5f9;
        color: #475569;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Typography Clamp */
    .clamped-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.4;
        height: 40px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 8px;
    }

    /* Smooth Buttons */
    .btn-premium-add {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-premium-add:hover {
        background: var(--primary-hover);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    /* Entrance Animation */
    @keyframes cardArrival {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-grid-item {
        animation: cardArrival 0.4s cubic-bezier(0.16, 1, 0.3, 1) backwards;
    }
</style>
@endpush

<div class="container-fluid py-4 px-3 px-lg-4">

    <!-- HERO PROFILE HEADER -->
    <div class="gallery-hero d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill" style="font-size: 10px; letter-spacing: 0.5px;">EX-DEMO & SECOND</span>
            </div>
            <h3 class="fw-bold text-white mb-1">Katalog Produk Retail</h3>
            <p class="text-white text-opacity-75 small mb-0">Manajemen kept unit, penawaran harga terbaik, dan status asuransi garansi aktif.</p>
        </div>

        @php
            $cartCount = $draftOrder?->items?->groupBy(fn($item) => $item->secondProduct->sales_order_number)->count() ?? 0;
        @endphp

        <div>
            <a href="{{ route('cart.index') }}" class="cart-glass-btn d-flex align-items-center gap-3 text-white text-decoration-none">
                <div class="position-relative">
                    <i class="bi bi-bag-check fs-4"></i>
                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-dark px-1.5 py-1" style="font-size: 9px;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </div>
                <div class="text-start d-none d-md-block">
                    <div class="text-white text-opacity-60" style="font-size: 10px; font-weight: 500; text-transform: uppercase;">Draft Booking</div>
                    <div class="fw-bold" style="font-size: 13px;">{{ $cartCount }} Grup SO</div>
                </div>
            </a>
        </div>
    </div>

    <!-- PRODUCT ADAPTIVE GRID -->
    <div class="row g-3 g-lg-4">
        @foreach ($seconds as $index => $second)
        <div class="col-6 col-sm-4 col-md-3 col-xl-2 animate-grid-item" style="animation-delay: {{ $index * 0.03 }}s">
            <div class="modern-product-card">

                <!-- STAGE IMAGE SIZING (SQUARE) -->
                <div class="image-container-ratio">
                    <span class="condition-badge">SECOND</span>
                    
                    <div class="abs-carousel-wrapper" id="product-gallery">
                        <div id="carousel-{{ $second->id }}" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                            <div class="carousel-inner h-100">
                                @forelse ($second->images as $key => $img)
                                    <div class="carousel-item h-100 {{ $key == 0 ? 'active' : '' }}">
                                        <a href="{{ $img->url }}" 
                                           data-pswp-width="{{ $img->width ?? 1200 }}" 
                                           data-pswp-height="{{ $img->height ?? 1200 }}"
                                           class="d-block h-100">
                                            <img src="{{ $img->url }}" class="product-img" alt="Product Visual Spec">
                                        </a>
                                    </div>
                                @empty
                                    <div class="carousel-item h-100 active">
                                        <a href="{{ asset('images/noimage.jpg') }}" data-pswp-width="1200" data-pswp-height="1200" class="d-block h-100">
                                            <img src="{{ asset('images/noimage.jpg') }}" class="product-img" alt="No Graphics Uploaded">
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD METRICS BODY -->
                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                    
                    <!-- TITLE CLAMP -->
                    <h6 class="clamped-title" title="{{ $second->item_name }}">
                        {{ $second->item_name }}
                    </h6>

                    <!-- IDENTIFIER STRIP -->
                    <div class="mb-2.5">
                        <span class="sn-code" title="SN: {{ $second->serial_number ?? '-' }}">
                            <i class="bi bi-hash text-muted me-0.5"></i>{{ $second->serial_number ?? '-' }}
                        </span>
                    </div>

                    <!-- WARRANTY REASSURANCE BOX -->
                    <div class="p-2 rounded-3 bg-light border border-light mb-3">
                        <div class="d-flex align-items-center gap-1.5 mb-1 text-truncate">
                            <i class="bi bi-shield-check text-success fs-7"></i>
                            <span class="fw-bold text-success" style="font-size: 11px; letter-spacing: 0.3px;">
                                {{ strtoupper($second->type_garansi ?? 'TANPA GARANSI') }}
                            </span>
                        </div>
                        <div class="text-muted text-truncate" style="font-size: 10.5px;">
                            <i class="bi bi-clock-history me-1"></i>
                            {{ $second->tanggal_fake ? \Carbon\Carbon::parse($second->tanggal_fake)->isoFormat('D MMM YYYY') : 'Expired / Matang' }}
                        </div>
                    </div>

                    <!-- PRICING & CALL-TO-ACTION STAGE -->
                    <div class="mt-auto pt-2 border-top border-light">
                        <div class="text-secondary mb-1" style="font-size: 10px; font-weight: 500; text-transform: uppercase;">Harga Jual</div>
                        <div class="fw-bold text-danger mb-2" style="font-size: 16px; letter-spacing: -0.3px;">
                            Rp {{ number_format($second->selling_price, 0, ',', '.') }}
                        </div>

                        <button onclick="keepBarang({{ $second->id }})" class="btn-premium-add w-100">
                            <i class="bi bi-cart-plus fs-6"></i> Keep Barang
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

@push('scripts')
<script>
async function keepBarang(id) {
    try {
        Swal.fire({
            title: 'Memproses Order...',
            text: 'Mengunci data serial item',
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
            title: 'Berhasil di-Keep',
            text: 'Data paket SO dimasukkan ke lembar draft.',
            timer: 1500,
            showConfirmButton: false
        }).then(() => location.reload());

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Eksekusi',
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
        pswpModule: () => import('https://cdn.jsdelivr.net/npm/photoswipe@5/dist/photoswipe.esm.min.js')
    });
    lightbox.init();
</script>
@endpush

@endsection