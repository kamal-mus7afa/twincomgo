@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Customer')

@section('content')

<div class="card p-4 rounded-4 shadow-sm border-0 mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3>Daftar Customer</h3>
            <p>Kelola daftar customer</p>
        </div>
        <div>
            <a href="{{route('customer.create')}}" class="btn btn-outline-primary rounded-3">
                Tambah
            </a>
        </div>
    </div>
</div>

<div class="card p-0 border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Customer</th>
                    <th>Nomor Customer</th>
                    <th>Handphone Customer</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                <tr>
                    <td>{{$customer->name}}</td>
                    <td>{{$customer->customer_number}}</td>
                    <td>{{$customer->phone}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection