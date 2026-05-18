@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Daftar Produk')

@section('content')

<form action="{{route('customer.store')}}" method="POST">
    @csrf
    <div class="d-flex gap-2">
        <input class="form-control" type="text" name="name" placeholder="Nama Customer">
        <input class="form-control" type="text" name="customer_number" placeholder="Nomor Customer">
        <input class="form-control" type="text" name="phone" placeholder="Nomor Handphone">
        <button class="btn btn-primary" type="submit">simpan</button>
    </div>
</form>

@endsection