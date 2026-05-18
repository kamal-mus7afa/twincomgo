@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')

<h1>Permission Log Activity</h1>

<form action="{{route('permission.index')}}" method="GET" class="d-flex gap-2 mb-2">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" id="search" class="form-control border-start-0" 
                    placeholder="Search by name or email..." value="{{ request('search') }}">
        </div>
    
    <button class="btn btn-success">Cari</button>
</form>

<form method="POST">
    @csrf
    <div style="max-height: 400px; overflow-y: auto;" class="rounded">

        <table class="table table-hover">

            <thead class="table-light sticky-top">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                </tr>
            </thead>

            <tbody>

                @foreach($users as $user)

                <tr>
                    <td class="text-center">

                        <input
                            type="checkbox"
                            name="users[]"
                            value="{{ $user->id }}"

                            {{ $user->hasPermissionTo('view log activity') ? 'checked' : '' }}
                        >

                    </td>

                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>
                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    <button type="submit" class="btn btn-primary mt-2">
        Save Permission
    </button>

</form>

@endsection