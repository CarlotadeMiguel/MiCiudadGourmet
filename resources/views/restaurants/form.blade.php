@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">{{ isset($restaurant) ? 'Editar' : 'Crear' }} Restaurante</h2>
            </div>
            <div class="card-body">
                <form method="POST" 
                      action="{{ isset($restaurant) ? route('restaurants.update', $restaurant) : route('restaurants.store') }}"
                      enctype="multipart/form-data">
                    @csrf
                    @if(isset($restaurant)) @method('PUT') @endif

                    <div class="form-floating mb-4">
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $restaurant->name ?? '') }}" 
                               placeholder="Nombre"
                               required>
                        <label for="name">Nombre del Restaurante</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="text" 
                               class="form-control" 
                               id="address" 
                               name="address" 
                               value="{{ old('address', $restaurant->address ?? '') }}" 
                               placeholder="Dirección"
                               required>
                        <label for="address">Dirección</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="tel" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $restaurant->phone ?? '') }}" 
                               placeholder="Teléfono">
                        <label for="phone">Teléfono (opcional)</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Categorías</label>
                        <div class="row g-2">
                            @foreach($categories as $category)
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="category_ids[]" 
                                               value="{{ $category->id }}" 
                                               id="category-{{ $category->id }}"
                                               {{ (isset($restaurant) && $restaurant->categories->contains($category)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="category-{{ $category->id }}">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="form-label fw-bold">Foto del Restaurante</label>
                        <input type="file" 
                               class="form-control" 
                               id="photo" 
                               name="photo" 
                               accept="image/*">
                        @if(isset($restaurant) && $restaurant->photos->first())
                            <div class="mt-3 text-center">
                                <img src="{{ $restaurant->photos->first()->url }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px;" 
                                     alt="Foto actual">
                            </div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ isset($restaurant) ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
