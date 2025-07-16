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
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @if($restaurant->reviews_count > 0)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill me-1"></i>
                                    {{ number_format($restaurant->reviews_avg_rating, 1) }}
                                </span>
                                <small class="text-muted ms-1">({{ $restaurant->reviews_count }})</small>
                            @else
                                <small class="text-muted">Sin reseñas</small>
                            @endif
                        </div>
                        <small class="text-muted">
                            {{ $restaurant->categories->pluck('name')->first() }}
                        </small>
                    </div>
                    
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
