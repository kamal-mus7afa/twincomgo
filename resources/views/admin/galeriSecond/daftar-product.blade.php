@extends('layouts.admin')

@section('page-title', 'Galeri Second')

@section('content')

<div class="container-fluid">
    <a href="{{ route('checkout') }}" class="position-relative">

        <i class="bi bi-cart3 fs-4"></i>

        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ $draftOrder?->items?->count() ?? 0 }}
        </span>

    </a>
    <div class="row g-4">

        @foreach ($seconds as $second)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">

                    {{-- IMAGE / CAROUSEL --}}
                    <div class="position-relative">

                        @if ($second->images && $second->images->count())

                            <div id="carousel-{{ $second->id }}" 
                                class="carousel slide" 
                                data-bs-ride="carousel">

                                <div class="carousel-inner">

                                    @foreach ($second->images as $key => $img)
                                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                            <img 
                                                src="{{ $img->url }}"
                                                class="d-block w-100"
                                                style="height:220px; object-fit:cover;"
                                                alt="{{ $second->item_name }}">
                                        </div>
                                    @endforeach

                                </div>

                            </div>

                        @else

                            <img 
                                src="{{ asset('images/no-image.png') }}"
                                class="card-img-top"
                                style="height:220px; object-fit:cover;">

                        @endif

                        {{-- BADGE --}}
                        <span class="position-absolute top-0 start-0 badge bg-dark m-2 px-3 py-2 rounded-pill">
                            Second
                        </span>

                    </div>

                    {{-- BODY --}}
                    <div class="card-body d-flex flex-column">

                        {{-- TITLE --}}
                        <div style="height:150px; overflow:hidden;">
                            <h6 class="fw-semibold mb-0 lh-sm">
                                {{ $second->item_name }}
                            </h6>
                        </div>

                        {{-- ITEM NO --}}
                        <small class="text-muted mt-2">
                            {{ $second->item_no }}
                        </small>

                        {{-- PRICE --}}
                        <div class="mt-3">
                            <span class="text-danger fw-bold fs-4">
                                Rp {{ number_format($second->selling_price, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- BUTTON --}}
                        <form action="{{ route('second.keep', $second->id) }}" method="POST">
                            @csrf

                            <button class="btn btn-warning rounded-pill w-100 mt-2">
                                Keep Barang
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

@endsection