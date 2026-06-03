{{-- 🔹 Tabel Desktop --}}
<div class="desktop-table" >
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Kode</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center" style="width: 10%">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr onclick="window.location='{{ 
                                        match(Auth::user()->status) {
                                            'admin'          => route('admin.detail', ['encrypted' => Hashids::encode($item['id'])]),
                                            'TWINCOM PATNER' => route('mitra.detail', ['encrypted' => Hashids::encode($item['id'])]), // Arahkan ke rute partner
                                            'RESELLER'       => route('mitra.detail', ['encrypted' => Hashids::encode($item['id'])]), // Arahkan ke rute reseller
                                            default          => route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]),
                                        }
                                    }}'" style="cursor: pointer;">
                                <td class="text-center" style="padding: 12px;"><span>{{ $item['no'] ?? '-' }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                </td>
                                <td class="td-harga">
                                    <div class="harga-grid">
                                        <span class="harga-rp">Rp</span>
                                        <span class="harga-nominal">
                                            <span 
                                                class="item-price"
                                                data-id="{{ $item['id'] }}"
                                                data-mode="{{ match($filters['priceMode'] ?? 'default') { 'reseller' => 'RESELLER', 'patner' => 'TWINCOM PATNER', default => 'USER' } }}"
                                                data-lazy-price
                                            >Loading…</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if (Auth::user()->status === 'admin' && Auth::user()->status === 'KARYAWAN')
                                        {{ $item['selected_stock'] ?? 0 }}
                                    @else
                                        {{ $item['availableToSell']}}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                    @endphp
                                    <span>{{ $unit }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                                    Tidak ada produk ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 📱 MOBILE -->
<div class="mobile-list">
    <div class="row g-2">
        @forelse($items as $item)
            <div class="col-12">
                <div class="product-card">
                    <div class="top-row">
                        <div class="product-title">{{ $item['name'] }}</div>
                        <div class="harga-grid">
                            <span class="harga-rp">Rp</span>
                            <span class="harga-nominal">
                                <span 
                                    class="item-price"
                                    data-id="{{ $item['id'] }}"
                                    data-mode="{{ $filters['priceMode'] === 'reseller' ? 'RESELLER' : 'USER' }}"
                                    data-lazy-price
                                >Loading…</span>
                            </span>
                        </div>
                    </div>

                    <div class="product-code text-muted small mt-1">{{ $item['no'] ?? '-' }}</div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="product-meta">
                            Stok: @if (Auth::user()->status === 'admin' && Auth::user()->status === 'KARYAWAN')
                                <strong>{{ $item['selected_stock'] ?? 0 }}</strong>
                            @else
                                <strong>{{ $item['availableToSell'] ?? 0 }}</strong>
                            @endif /
                            <strong>
                                @php
                                    $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                @endphp
                                {{ $unit }}
                            </strong>
                        </div>
                        <a href="{{ Auth::user()->status === 'RESELLER' 
                                ? route('mitra.detail', ['encrypted' => Hashids::encode($item['id'])]) 
                                : route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]) }}" class="btn btn-success btn-sm btn-detail">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                    Tidak ada produk ditemukan.
                </div>
            </div>
        @endforelse
    </div>
</div>