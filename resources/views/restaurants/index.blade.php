@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h2">🍴 Restaurantes Destacados</h1>
        @auth
            <a href="{{ route('restaurants.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Restaurante
            </a>
        @endauth
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    @foreach($restaurants as $restaurant)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm">
                @if($restaurant->photos->first())
                    <img src="{{ $restaurant->photos->first()->url }}" 
                         class="card-img-top object-fit-cover" 
                         style="height: 200px;" 
                         alt="{{ $restaurant->name }}">
                @else
                    <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="bi bi-camera fs-1"></i>
                    </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $restaurant->name }}</h5>
                    <p class="card-text text-muted">
                        <i class="bi bi-geo-alt me-1"></i>{{ $restaurant->address }}
                    </p>
                    <div class="d-grid">
                        <a href="{{ route('restaurants.show', $restaurant) }}" 
                           class="btn btn-outline-primary">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
