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
                                <div class="mb-4">
                                    <h5 class="mb-3">Opiniones de los usuarios</h5>
                                    @php
                                        $reviews = $restaurant->reviews->sortByDesc('created_at');
                                        $topReviews = $reviews->take(5);
                                        $remainingReviews = $reviews->count() > 5 ? $reviews->slice(5) : collect();
                                    @endphp
                                    
                                    @forelse($topReviews as $review)
                                        <div class="card mb-3 border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <span class="badge bg-warning text-dark me-2">
                                                            <i class="bi bi-star-fill"></i> {{ $review->rating }}/5
                                                        </span>
                                                        <small class="text-muted">
                                                            {{ $review->user->name }} - {{ $review->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                    @auth
                                                        @if(auth()->id() === $review->user_id)
                                                            <a href="{{ route('restaurants.show', $restaurant) }}?edit_review={{ $review->id }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-pencil-square"></i> Editar
                                                            </a>
                                                        @endif
                                                    @endauth
                                                </div>
                                                <p class="mb-0">{{ $review->comment }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">Aún no hay reseñas para este restaurante.</p>
                                    @endforelse
                                    
                                    @if($remainingReviews->count() > 0)
                                        <div class="text-center mt-3">
                                            <button class="btn btn-sm btn-outline-secondary" id="show-more-reviews">
                                                Ver más reseñas ({{ $remainingReviews->count() }} más)
                                            </button>
                                        </div>
                                        
                                        <div id="more-reviews" class="d-none mt-3">
                                            @foreach($remainingReviews as $review)
                                                <div class="card mb-3 border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div>
                                                                <span class="badge bg-warning text-dark me-2">
                                                                    <i class="bi bi-star-fill"></i> {{ $review->rating }}/5
                                                                </span>
                                                                <small class="text-muted">
                                                                    {{ $review->user->name }} - {{ $review->created_at->diffForHumans() }}
                                                                </small>
                                                            </div>
                                                            @auth
                                                                @if(auth()->id() === $review->user_id)
                                                                    <a href="{{ route('restaurants.show', $restaurant) }}?edit_review={{ $review->id }}" class="btn btn-sm btn-outline-primary">
                                                                        <i class="bi bi-pencil-square"></i> Editar
                                                                    </a>
                                                                @endif
                                                            @endauth
                                                        </div>
                                                        <p class="mb-0">{{ $review->comment }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @auth
                                    @php
                                        $userReview = $restaurant->reviews->where('user_id', auth()->id())->first();
                                    @endphp
                                    
                                    @if(auth()->id() !== $restaurant->user_id)
                                        @if(!$userReview)
                                            <div class="mt-4">
                                                <a href="{{ route('restaurants.show', $restaurant) }}?review_form=1" class="btn btn-primary mb-3">
                                                    <i class="bi bi-star-fill me-2"></i>Deja tu opinión
                                                </a>
                                                
                                                <div id="review-form-container" class="{{ !request()->has('review_form') ? 'd-none' : '' }}">
                                                    <h5>Deja tu reseña</h5>
                                                    <form action="{{ route('reviews.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                                                        
                                                        <div class="mb-3">
                                                            <label for="rating" class="form-label">Calificación</label>
                                                            <select class="form-select" id="rating" name="rating" required>
                                                                <option value="">Selecciona una calificación</option>
                                                                <option value="1">1 - Muy malo</option>
                                                                <option value="2">2 - Malo</option>
                                                                <option value="3">3 - Regular</option>
                                                                <option value="4">4 - Bueno</option>
                                                                <option value="5">5 - Excelente</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="comment" class="form-label">Comentario</label>
                                                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                                        </div>
                                                        
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-primary">Enviar reseña</button>
                                                            <a href="{{ route('restaurants.show', $restaurant) }}" class="btn btn-outline-secondary">Cancelar</a>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endauth
                                
                                <!-- Formulario para editar reseña -->
                                @auth
                                    @if(request()->has('edit_review'))
                                        @php
                                            $editingReview = \App\Models\Review::find(request()->edit_review);
                                        @endphp
                                        @if($editingReview && $editingReview->user_id === auth()->id())
                                            <div class="card mt-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0">Editar tu reseña</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form action="{{ route('reviews.update', $editingReview) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        
                                                        <div class="mb-3">
                                                            <label for="rating" class="form-label">Calificación</label>
                                                            <select class="form-select" id="rating" name="rating" required>
                                                                <option value="1" {{ $editingReview->rating == 1 ? 'selected' : '' }}>1 - Muy malo</option>
                                                                <option value="2" {{ $editingReview->rating == 2 ? 'selected' : '' }}>2 - Malo</option>
                                                                <option value="3" {{ $editingReview->rating == 3 ? 'selected' : '' }}>3 - Regular</option>
                                                                <option value="4" {{ $editingReview->rating == 4 ? 'selected' : '' }}>4 - Bueno</option>
                                                                <option value="5" {{ $editingReview->rating == 5 ? 'selected' : '' }}>5 - Excelente</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="comment" class="form-label">Comentario</label>
                                                            <textarea class="form-control" id="comment" name="comment" rows="3" required>{{ $editingReview->comment }}</textarea>
                                                        </div>
                                                        
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                                            <a href="{{ route('restaurants.show', $restaurant) }}" class="btn btn-outline-secondary">Cancelar</a>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Mostrar más reseñas
        const showMoreBtn = document.getElementById('show-more-reviews');
        const moreReviews = document.getElementById('more-reviews');
        
        if (showMoreBtn && moreReviews) {
            showMoreBtn.addEventListener('click', function() {
                moreReviews.classList.toggle('d-none');
                if (moreReviews.classList.contains('d-none')) {
                    showMoreBtn.textContent = 'Ver más reseñas';
                } else {
                    showMoreBtn.textContent = 'Ver menos reseñas';
                }
            });
        }
    });
</script>
@endpush
