<style>
    .katalog-scroll {
        height: calc(100vh - 280px);
        overflow-y: auto;
        scrollbar-width: none;
    }
    .katalog-scroll::-webkit-scrollbar {
        display: none;
    }
</style>
<div class="katalog-scroll">
    <div class="row g-2"
        data-total="{{ $items->count() }}"
        data-original="{{ $totalItems ?? 0 }}">

    @forelse ($items as $item)
        @php
            $url = Auth::user()->status === 'RESELLER'
                ? route('reseller.detail', ['encrypted' => Hashids::encode($item['id'])])
                : route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]);

            $unit = preg_replace(
                '/^[\d.,]+\s*(?=PCS\b)/i',
                '',
                trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-'))
            );

            $image = $item['detailItemImage']['fileName'] ?? null;
        @endphp

        <div class="col-6 col-md-4 col-lg-3 col-xl-3">
            <div class="card h-100 shadow-sm border-0 rounded-2 katalog-card"
                {{-- onclick="window.location='{{ $url }}'" --}}
                >
                {{-- IMAGE --}}
                <div class="ratio ratio-4x3 mb-2">
                    <img
                        src="{{ $item['image']
                            ? route('proxy.image', [
                                'file' => $item['image'],
                                'session' => $session,
                            ])
                            : asset('images/noimage.jpg')
                        }}"
                        class="img-fluid w-full rounded-top"
                        style="object-fit:contain"
                        loading="lazy"
                    >
                </div>
                <div class="card-body d-flex flex-column p-2">
                    {{-- NAMA & KATEGORI --}}
                    <div class="mb-2">
                        <div class="fw-semibold" style="font-size: 0.85rem; line-height: 1.2;">
                            {{ $item['name'] }}
                        </div>
                        @if(!empty($item['itemCategory']['name']))
                            <small class="text-muted" style="font-size: 0.75rem;">
                                {{ $item['itemCategory']['name'] }}
                            </small>
                        @endif
                    </div>
                    {{-- HARGA --}}
                    <div class="mt-auto">
                        <div class="product-code text-muted small mt-1">SKU : {{ $item['no'] ?? '-' }}</div>
                        {{-- STOK & SATUAN --}}
                        <div class="d-flex justify-content-between text-muted small" style="font-size: 0.9rem;">
                            <div class="fw-bold text-primary mb-1" style="font-size: 0.9rem;">
                                Rp
                                <span
                                    class="item-price"
                                    data-id="{{ $item['id'] }}"
                                    data-mode="{{ $filters['priceMode'] === 'reseller' ? 'RESELLER' : 'USER' }}"
                                    data-lazy-price
                                >Loading…</span>
                            </div>
                            <span>{{ $item['availableToSell'] ?? 0 }} {{ $unit }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-box-seam display-6 d-block mb-2"></i>
            Tidak ada produk ditemukan.
        </div>
    @endforelse
        <div class="col-12">
            <div id="infinite-loader" class="text-center py-3 d-none">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>
        </div>
    </div>
</div>