<form method="GET" class="filter-card shadow p-3 rounded-3" id="filter-form">
    
    {{-- STOK --}}
    <div class="mb-3">
        <label class="form-label">Stok Ready</label>
        <select name="stok_ada" class="form-select shadow-sm">
            <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>Ya</option>
            <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
        </select>
    </div>

    {{-- MIN HARGA --}}
    <div class="mb-3">
        <label class="form-label">Min Harga</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="min_price"
                   class="form-control shadow-sm"
                   value="{{ request('min_price') }}"
                   placeholder="0"
                   oninput="formatRupiahFilter(this)">
        </div>
    </div>

    {{-- MAX HARGA --}}
    <div class="mb-3">
        <label class="form-label">Max Harga</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="max_price"
                   class="form-control shadow-sm"
                   value="{{ request('max_price') }}"
                   placeholder="0"
                   oninput="formatRupiahFilter(this)">
        </div>
    </div>

    {{-- KATEGORI --}}
    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="category_id[]" class="form-select shadow-sm" id="category_search" multiple>
            @foreach($categories as $cat)
                <option value="{{ $cat['id'] }}"
                    {{ collect(request('category_id'))->contains($cat['id']) ? 'selected' : '' }}>
                    {{ $cat['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- SEARCH --}}
    <div class="mb-3">
        <label class="form-label">Pencarian</label>
        <input type="text" name="search"
               class="form-control shadow-sm"
               value="{{ request('search') }}"
               placeholder="Kode / Nama barang">
    </div>

    {{-- BUTTON --}}
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100 shadow-sm">
            <i class="bi bi-search"></i>
        </button>
        <a href="{{ route('katalog.items') }}" class="btn btn-secondary w-100 shadow-sm">
            <i class="bi bi-arrow-counterclockwise"></i>
        </a>
    </div>

</form>

