@extends('layouts.admin')

@section('title', 'Akses')

@section('content')

<div class="d-flex justify-content-between align-items-center">
    <div>
        <h3>Akses</h3>
        <p>Menambahkan akses untuk user</p>
    </div>
    <div>
        <a href="{{route('permission.index')}}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

<div class="card p-3">
    <form action="{{ route('permission.store') }}" method="POST">
        @csrf

        <table class="table table-borderless">
            <thead>
                <tr>
                    <th>Nama akses</th>
                    <th class="text-center" style="width: 70px">Aktif</th>
                    <th class="text-center" style="width: 70px">Lihat</th>
                    <th class="text-center" style="width: 70px">Ubah</th>
                    <th class="text-center" style="width: 70px">Tambah</th>
                    <th class="text-center" style="width: 70px">Hapus</th>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center">
                    <td>
                        <input type="text" name="module" class="form-control w-50" placeholder="Contoh : log-activity">
                    </td>
                    <td>
                        <input type="checkbox" name="actions[]" value="aktif">
                    </td>
                    <td>
                        <input type="checkbox" name="actions[]" value="view">
                    </td>
                    <td>
                        <input type="checkbox" name="actions[]" value="edit">
                    </td>
                    <td>
                        <input type="checkbox" name="actions[]" value="create">
                    </td>
                    <td>
                        <input type="checkbox" name="actions[]" value="delete">
                    </td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-primary">Simpan</button>
    </form>
</div>

@endsection