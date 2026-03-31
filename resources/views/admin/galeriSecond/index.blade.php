@extends('layouts.admin')

@section('page-title', 'Galeri Second')

@section('content')


<div class="p-4">
    <div class="mb-4">
        <h5 class="mb-0">Tambah Barang Second</h5>
    </div>

    <div class="card-body">

        <div class="row">
            <!-- ITEM -->
            <div class="col">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Barang</label>
                    <select id="item_select" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Serial Number (SN)</label>
                    <select id="sn_select" class="form-select"></select>
                </div>
                <!-- GAMBAR -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Upload Gambar</label>
                    <input type="file" name="gambar" id="gambar" class="form-control">
                </div>
            </div>
    
            <!-- SN -->
            <div class="col">
                <!-- GARANSI -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="checkChecked">
                        <label class="form-check-label fw-semibold" for="checkChecked">
                            Garansi
                        </label>
                    </div>
                </div>
        
                <!-- INPUT GARANSI -->
                <div id="input_garansi" class="border rounded p-3 bg-light mb-4" style="display: none;">
                    
                    <div class="mb-3">
                        <label class="form-label">Tipe Garansi</label>
                        <select name="type_garansi" id="type_garansi" class="form-select">
                            <option value="">Pilih Tipe</option>
                            <option value="resmi">Resmi</option>
                            <option value="distributor">Distributor</option>
                        </select>
                    </div>
        
                    <div>
                        <label class="form-label">Tanggal Garansi</label>
                        <input type="date" name="tanggal_garansi" id="tanggal_garansi" class="form-control">
                    </div>
                </div>
            </div>
        </div>



    </div>

    <div class="card-footer text-start">
        <button class="btn btn-primary">
            Simpan
        </button>
    </div>
</div>

@push('scripts')  
<script>
let itemTom;
let snTom;

document.addEventListener("DOMContentLoaded", function () {

    const checkbox = document.getElementById('checkChecked');
    const inputGaransi = document.getElementById('input_garansi');

    checkbox.addEventListener('change', function () {
        if (this.checked) {
            inputGaransi.style.display = 'block';
        } else {
            inputGaransi.style.display = 'none';
        }
    });

    // ✅ INIT SN SELECT (WAJIB DULU)
    snTom = new TomSelect("#sn_select", {
        valueField: "value",
        labelField: "text",
        searchField: ["text"],
        optgroupField: "warehouse",
        optgroups: [],
        options: []
    });

    // ✅ INIT ITEM SELECT
    itemTom = new TomSelect("#item_select", {
        valueField: "no",
        labelField: "text",
        searchField: ["text"],
        maxOptions: 20,

        load: function(query, callback) {
            if (!query.length) return callback();

            fetch(`/admin/galeri/list?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    callback(data.map(item => ({
                        no: item.no,
                        text: `${item.name} (${item.no})`
                    })));
                })
                .catch(() => callback());
        },

        onChange: function(value) {
            if (value) loadSN(value);
        }
    });

});

function loadSN(itemNo) {

    snTom.clear();
    snTom.clearOptions();
    snTom.clearOptionGroups();

    fetch(`/admin/galeri/sn?itemNo=${itemNo}`)
        .then(res => res.json())
        .then(data => {

            if (!data.length) {
                snTom.addOption({
                    value: '',
                    text: 'Tidak ada SN'
                });
                return;
            }

            let groups = {};

            data.forEach(item => {
                let warehouse = item.warehouse.name;
                let sn = item.serialNumber.number;

                // simpan group
                if (!groups[warehouse]) {
                    groups[warehouse] = true;

                    snTom.addOptionGroup(warehouse, {
                        value: warehouse,
                        label: warehouse
                    });
                }

                // tambah SN ke group
                snTom.addOption({
                    value: sn,
                    text: sn,
                    warehouse: warehouse
                });
            });

            snTom.refreshOptions(false);
        });
}
</script>
@endpush

@endsection