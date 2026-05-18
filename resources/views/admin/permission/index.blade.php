@extends('layouts.admin')

@section('title', 'Permission User')

@section('content')

<div class="d-flex justify-content-between align-items-center">
    <div class="">
        <h4><i class="bi bi-person-lock"></i> Akses</h4>
        <p>Kelola akses untuk pengguna</p>
    </div>

    <div>
        <a href="{{route('permission.create')}}" class="btn btn-primary shadow">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>
</div>
<div class="card overflow-hidden shadow">
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr class="table-dark">
                    <th class="text-center" style="width: 50px">#</th>
                    <th>Name</th>
                    <th class="text-center" style="width: 250px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                    <tr>
                        <td class="text-center">{{$loop->iteration}}</td>
                        <td>{{$permission->name}}</td>
                        <td class="text-center">
                            <form id="delete-form-{{ $permission->id }}" action="{{ route('permission.delete', $permission->id) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="button" onclick="confirmDelete({{ $permission->id }})" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin mau hapus?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush

@endsection