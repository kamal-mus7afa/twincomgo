@extends('layouts.admin')

@section('page-title', 'Daftar Order')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Daftar Order
            </h3>

            <p class="text-muted mb-0">
                Monitoring seluruh sales order
            </p>

        </div>

    </div>

    <!-- Card -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead class="bg-light">

                        <tr>

                            <th class="px-4 py-3">
                                SO Number
                            </th>

                            <th class="py-3">
                                Customer
                            </th>

                            <th class="py-3">
                                Total Item
                            </th>

                            <th class="py-3">
                                Dibuat Oleh
                            </th>

                            <th class="py-3">
                                Status
                            </th>

                            <th class="text-center py-3">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($orders as $order)

                            <tr>

                                <td class="px-4">

                                    <div class="fw-semibold">
                                        {{ $order->accurate_so_number ?? '-' }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $order->created_at->format('d M Y H:i') }}
                                    </small>

                                </td>

                                <td>

                                    <div class="fw-semibold">
                                        {{ $order->customer_no }}
                                    </div>

                                </td>

                                <td>

                                    <span class="badge bg-dark rounded-pill px-3 py-2">
                                        {{ $order->items->count() }} Item
                                    </span>

                                </td>

                                <td>

                                    {{ $order->user->name ?? '-' }}

                                </td>

                                <td>

                                    @if($order->status == 'DRAFT')

                                        <span class="badge bg-secondary">
                                            DRAFT
                                        </span>

                                    @elseif($order->status == 'PENDING_SYNC')

                                        <span class="badge bg-warning text-dark">
                                            PENDING
                                        </span>

                                    @elseif($order->status == 'CHECKOUT')

                                        <span class="badge bg-success">
                                            CHECKOUT
                                        </span>

                                    @elseif($order->status == 'FAILED_SYNC')

                                        <span class="badge bg-danger">
                                            FAILED
                                        </span>

                                    @else

                                        <span class="badge bg-dark">
                                            {{ $order->status }}
                                        </span>

                                    @endif

                                </td>

                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a
                                            href=""
                                            class="btn btn-sm btn-outline-dark rounded-3"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <button
                                            class="btn btn-outline-success btn-sm rounded-3"
                                            onclick="dealOrder({{ $order->id }})"
                                            title="Deal"
                                        >
                                            <i class="bi bi-check2-all"></i>
                                        </button>

                                        <button
                                            class="btn btn-outline-danger btn-sm rounded-3"
                                            onclick="cancelOrder({{ $order->id }})"
                                            title="Cancel"
                                        >
                                            <i class="bi bi-x-square"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <div class="text-muted">

                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>

                                        Belum ada data order

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>

    function dealOrder(id)
    {
        Swal.fire({
            title: 'Deal Order?',
            text: 'Order akan diproses menjadi DEAL',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Deal',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/order/${id}/deal`, {

                method: 'POST',

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
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            });
        });
    }

    function cancelOrder(id)
    {
        Swal.fire({
            title: 'Batalkan Order?',
            text: 'Barang akan kembali READY',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Cancel',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/order/${id}/cancel`, {

                method: 'POST',

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
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            });
        });
    }

</script>
@endpush

@endsection