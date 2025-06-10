@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            @if($restaurant->photos->first())
                <img src="{{ $restaurant->photos->first()->url }}" 
                     class="card-img-top object-fit-cover" 
                     style="height: 300px;" 
                     alt="{{ $restaurant->name }}">
            @endif
            
            <!-- Botones de edición/eliminación -->
            <div class="position-absolute top-0 end-0 mt-3 me-3">
                @if(auth()->check() && auth()->id() === $restaurant->user_id)
                <div class="d-flex gap-2">
                    <a href="{{ route('restaurants.edit', $restaurant) }}" 
                       class="btn btn-primary btn-lg shadow-sm"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="left" 
                       title="Editar restaurante">
                        <i class="bi bi-pencil-square me-2"></i>Editar
                    </a>
                    
                    <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger btn-lg shadow-sm"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="left" 
                                title="Eliminar restaurante">
                            <i class="bi bi-trash"></i>Delete
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div class="card-body">
                <!-- Encabezado -->
                <div class="mb-4">
                    <h1 class="display-4 fw-bold text-primary">{{ $restaurant->name }}</h1>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <p class="lead text-muted mb-0">
                            <i class="bi bi-geo-alt fs-5 me-2"></i>{{ $restaurant->address }}
                        </p>
                        @if($restaurant->phone)
                        <p class="text-muted mb-0">
                            <i class="bi bi-telephone fs-5 me-2"></i>{{ $restaurant->phone }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Categorías y Reseñas -->
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="bi bi-tags me-2"></i>Categorías</h3>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach($restaurant->categories as $category)
                                    <li class="list-group-item">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><i class="bi bi-star me-2"></i>Reseñas</h3>
                            </div>
                            <div class="card-body">
                                @forelse($restaurant->reviews as $review)
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-warning text-dark me-2">
                                                    ⭐ {{ $review->rating }}/5
                                                </span>
                                                <small class="text-muted">
                                                    {{ $review->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <p class="mb-0">{{ $review->comment }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">Aún no hay reseñas para este restaurante.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
