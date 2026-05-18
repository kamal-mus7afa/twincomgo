@extends('layouts.admin')

@section('title', 'Permission User')

@section('content')

<div class="d-flex justify-content-between align-items-center">
    <div>
        <h3><i class="bi bi-key"></i> Akses {{$user->name}}</h3>
        <p>Pilih salah satu akses untuk user</p>
    </div>
</div>

<form action="{{ route('permission.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row align-items-start">

        <!-- TABLE -->
        <div class="col-md-11">
            <div class="card p-0 overflow-hidden rounded shadow">
                <table class="table table-striped">
                    <thead>
                        <tr class="table-dark">
                            <th>Module</th>
                            <th class="text-center" style="width: 100px">Aktif</th>
                            <th class="text-center" style="width: 100px">Lihat</th>
                            <th class="text-center" style="width: 100px">Ubah</th>
                            <th class="text-center" style="width: 100px">Tambah</th>
                            <th class="text-center" style="width: 100px">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach($groupedPermissions as $module => $perms)

                        @php
                            $map = collect($perms)->keyBy('action');
                        @endphp

                        <tr>
                            <td class="text-start">
                                {{ ucwords(str_replace('-', ' ', $module)) }}
                            </td>

                            @foreach(['aktif','view','edit','create','delete'] as $action)
                                @php $perm = $map->get($action); @endphp

                                <td class="text-center">
                                    @if($perm)
                                        <input type="checkbox"
                                            name="permissions[]"
                                            value="{{ $perm['name'] }}"
                                            {{ $user->hasPermissionTo($perm['name']) ? 'checked' : '' }}>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="col-md-1 d-flex justify-content-center">
            <button class="btn btn-primary w-100 shadow">
                Save
            </button>
        </div>

    </div>
</form>

<script>
function toggleModule(source) {
    let container = source.closest('div').parentElement.querySelector('.module-permissions');
    let checkboxes = container.querySelectorAll('input[type="checkbox"]');

    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>

@endsection