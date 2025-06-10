@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Restaurantes</h1>
            @auth
            <button class="btn btn-primary mb-3"
            onclick="window.location='{{ route('restaurants.create') }}'">
                Nuevo Restaurante
            </button>
            @endauth
        </div>
    </div>

    <div class="row">
        @foreach($restaurants as $restaurant)
            <div class="col-md-4 mb-4">
                <div class="card">
                    @if($restaurant->photos->first())
                        <img src="{{ $restaurant->photos->first()->url }}" class="card-img-top" alt="{{ $restaurant->name }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $restaurant->name }}</h5>
                        <p class="card-text">{{ $restaurant->address }}</p>
                        <a href="{{ route('restaurants.show', $restaurant) }}" class="btn btn-primary">Ver Detalles</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
