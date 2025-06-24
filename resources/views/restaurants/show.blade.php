@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow mb-4">
            @if($photo = $restaurant->photos->first())
                <div class="ratio ratio-16x9">
                    <img src="{{ $photo->url }}"
                         class="card-img-top object-fit-cover"
                         alt="{{ $restaurant->name }}">
                </div>
            @endif

            <div class="card-header bg-transparent position-relative">
                <h1 class="h3 mb-0 text-primary">{{ $restaurant->name }}</h1>

                @can('update', $restaurant)
                    <div class="position-absolute top-0 end-0 p-2">
                        <a href="{{ route('restaurants.edit', $restaurant) }}"
                           class="btn btn-sm btn-outline-primary me-2"
                           data-bs-toggle="tooltip"
                           title="Editar restaurante">
                            <i class="bi bi-pencil-square"></i>
                        </a>

                        <form action="{{ route('restaurants.destroy', $restaurant) }}"
                              method="POST"
                              class="d-inline-block"
                              onsubmit="return confirm('¿Eliminar restaurante?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="tooltip"
                                    title="Eliminar restaurante">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                @endcan
            </div>

            <div class="card-body">
                <p class="text-muted mb-2">
                    <i class="bi bi-geo-alt me-1"></i>{{ $restaurant->address }}
                    @if($restaurant->phone)
                        <i class="bi bi-telephone ms-3 me-1"></i>{{ $restaurant->phone }}
                    @endif
                </p>

                @if($restaurant->description)
                    <p class="mb-0">{{ $restaurant->description }}</p>
                @endif

                <div class="row mt-4">
                    {{-- Categorías --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0">
                            <div class="card-header bg-primary text-white p-2">
                                <i class="bi bi-tags me-1"></i>Categorías
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach($restaurant->categories as $category)
                                    <li class="list-group-item">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Reseñas --}}
                    <div class="col-md-6">
                        <div class="card border-0">
                            <div class="card-header bg-primary text-white p-2">
                                <i class="bi bi-star me-1"></i>Reseñas
                            </div>
                            <div class="card-body p-2">
                                @php
                                    $reviews     = $restaurant->reviews->sortByDesc('created_at');
                                    $preview     = $reviews->take(5);
                                    $moreReviews = $reviews->slice(5);
                                @endphp

                                @forelse($preview as $review)
                                    <div class="d-flex mb-2">
                                        <span class="badge bg-warning text-dark me-2">
                                            <i class="bi bi-star-fill"></i> {{ $review->rating }}/5
                                        </span>
                                        <small class="text-muted">
                                            {{ $review->user->name }} · {{ $review->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="small mb-3">{{ $review->comment }}</p>
                                @empty
                                    <p class="text-muted">Aún no hay reseñas.</p>
                                @endforelse

                                @if($moreReviews->isNotEmpty())
                                    <button class="btn btn-sm btn-outline-secondary mb-2" id="toggle-reviews">
                                        Ver más ({{ $moreReviews->count() }})
                                    </button>
                                    <div id="additional-reviews" class="d-none">
                                        @foreach($moreReviews as $review)
                                            <div class="mb-2">
                                                <span class="badge bg-warning text-dark me-2">
                                                    <i class="bi bi-star-fill"></i> {{ $review->rating }}/5
                                                </span>
                                                <small class="text-muted">
                                                    {{ $review->user->name }} · {{ $review->created_at->diffForHumans() }}
                                                </small>
                                                <p class="small">{{ $review->comment }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div> {{-- /.card-body --}}
        </div> {{-- /.card --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Tooltip(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

        document.getElementById('toggle-reviews')?.addEventListener('click', function() {
            document.getElementById('additional-reviews')?.classList.toggle('d-none');
            this.textContent = this.textContent.includes('Ver más') 
                ? `Ver menos (${this.getAttribute('data-count')})` 
                : `Ver más (${this.getAttribute('data-count')})`;
        });
    });
</script>
@endpush
