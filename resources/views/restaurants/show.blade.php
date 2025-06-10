@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>{{ $restaurant->name }}</h1>
            @if($restaurant->photos->first())
                <img src="{{ $restaurant->photos->first()->url }}" class="img-fluid mb-3" alt="Foto restaurante">
            @endif
            <p class="lead">{{ $restaurant->address }}</p>
            <p>Teléfono: {{ $restaurant->phone ?? 'No disponible' }}</p>
            
            @can('update', $restaurant)
                <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn btn-warning">Editar</a>
            @endcan
            
            @can('delete', $restaurant)
                <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h3>Categorías</h3>
            <ul>
                @foreach($restaurant->categories as $category)
                    <li>{{ $category->name }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="col-md-6">
            <h3>Reseñas</h3>
            @foreach($restaurant->reviews as $review)
                <div class="card mb-2">
                    <div class="card-body">
                        <h5 class="card-title">⭐ {{ $review->rating }}/5</h5>
                        <p class="card-text">{{ $review->comment }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
