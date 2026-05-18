@extends('layouts.admin')

@section('page-title', 'Update Barang Second')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <!-- Header -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                        <div>

                            <h3 class="fw-bold mb-1">
                                Update Barang Second
                            </h3>

                            <p class="text-muted mb-0">
                                Lengkapi informasi barang second
                            </p>

                        </div>

                        <a
                            href="{{ route('second.index') }}"
                            class="btn btn-outline-secondary rounded-3"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali
                        </a>

                    </div>

                </div>

            </div>

            <!-- Error -->
            @if ($errors->any())

                <div class="alert alert-danger rounded-4">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <!-- Form -->
            <form
                action="{{ route('second.update', $second->id) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf
                @method('PUT')

                <div class="row">

                    <!-- LEFT -->
                    <div class="col-lg-7">

                        <div class="card border-0 shadow-sm rounded-4 mb-4">

                            <div class="card-body p-4">

                                <h5 class="fw-semibold mb-4">
                                    Informasi Barang
                                </h5>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Serial Number
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control rounded-3"
                                        value="{{ $second->serial_number }}"
                                        readonly
                                    >

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Nama Barang
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control rounded-3"
                                        value="{{ $second->item_name }}"
                                        readonly
                                    >

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Deskripsi
                                    </label>

                                    <textarea
                                        name="description"
                                        rows="5"
                                        class="form-control rounded-3"
                                        placeholder="Masukkan deskripsi barang..."
                                    >{{ old('description', $second->description) }}</textarea>

                                </div>

                            </div>

                        </div>

                        <!-- Upload Image -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">

                            <div class="card-body p-4">

                                <h5 class="fw-semibold mb-4">
                                    Upload Gambar
                                </h5>

                                <input
                                    type="file"
                                    name="images[]"
                                    class="form-control rounded-3"
                                    multiple
                                    accept="image/*"
                                >

                                <small class="text-muted">
                                    Bisa upload multiple gambar
                                </small>

                                @if($second->images->count())

                                    <div class="row mt-4">

                                        @foreach($second->images as $image)

                                            <div class="col-md-4 mb-3">

                                                <div class="border rounded-3 overflow-hidden position-relative">

                                                    <img
                                                        src="{{ $image->url }}"
                                                        class="img-fluid"
                                                        style="
                                                            height:180px;
                                                            width:100%;
                                                            object-fit:cover;
                                                        "
                                                    >

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2"
                                                        onclick="deleteImage({{ $image->id }})"
                                                    >
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>

                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="col-lg-5">

                        <div class="card border-0 shadow-sm rounded-4 mb-4">

                            <div class="card-body p-4">

                                <h5 class="fw-semibold mb-4">
                                    Garansi
                                </h5>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Tipe Garansi
                                    </label>

                                    <select
                                        name="type_garansi"
                                        class="form-select rounded-3"
                                    >

                                        <option value="">
                                            Pilih Tipe Garansi
                                        </option>

                                        <option
                                            value="resmi"
                                            {{ old('type_garansi', $second->type_garansi) == 'resmi' ? 'selected' : '' }}
                                        >
                                            Resmi
                                        </option>

                                        <option
                                            value="distributor"
                                            {{ old('type_garansi', $second->type_garansi) == 'distributor' ? 'selected' : '' }}
                                        >
                                            Distributor
                                        </option>

                                    </select>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Berlaku Sampai
                                    </label>

                                    <input
                                        type="date"
                                        name="tanggal_real"
                                        class="form-control rounded-3"
                                        value="{{ old('tanggal_real', $second->tanggal_real) }}"
                                    >

                                </div>

                            </div>

                        </div>

                        <!-- Status -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">

                            <div class="card-body p-4">

                                <h5 class="fw-semibold mb-4">
                                    Informasi Status
                                </h5>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Status
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control rounded-3"
                                        value="{{ strtoupper($second->status) }}"
                                        readonly
                                    >

                                </div>

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Sales Order
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control rounded-3"
                                        value="{{ $second->sales_order_number }}"
                                        readonly
                                    >

                                </div>

                            </div>

                        </div>

                        <!-- Submit -->
                        <div class="card border-0 shadow-sm rounded-4">

                            <div class="card-body p-4">

                                <button
                                    type="submit"
                                    class="btn btn-dark w-100 rounded-3 py-3"
                                >
                                    <i class="bi bi-check-circle me-2"></i>
                                    Update Barang
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>
@push('scripts')
<script>
    function deleteImage(id)
    {
        Swal.fire({
            title: 'Hapus Gambar?',
            text: 'Gambar yang dihapus tidak bisa dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/second-image/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {

                Swal.close();

                if (data.success) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Gambar berhasil dihapus',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message ?? 'Gagal menghapus gambar'
                    });
                }
            })
            .catch(() => {

                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan server'
                });
            });
        });
    }
</script>
@endpush

@endsection