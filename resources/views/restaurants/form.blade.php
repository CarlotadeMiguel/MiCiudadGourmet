@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>{{ isset($restaurant) ? 'Editar' : 'Crear' }} Restaurante</h1>
            
            <form method="POST"
                  action="{{ isset($restaurant) ? route('restaurants.update', $restaurant) : route('restaurants.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($restaurant))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $restaurant->name ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $restaurant->address ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone', $restaurant->phone ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Categorías</label>
                    @foreach($categories as $category)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category_ids[]" 
                                   value="{{ $category->id }}" 
                                   id="category-{{ $category->id }}"
                                   {{ (isset($restaurant) && $restaurant->categories->contains($category)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="category-{{ $category->id }}">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    @if(isset($restaurant) && $restaurant->photos->first())
                        <img src="{{ $restaurant->photos->first()->url }}" class="img-thumbnail mt-2" style="max-width: 200px;">
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($restaurant) ? 'Actualizar' : 'Guardar' }}</button>
            </form>
        </div>
    </div>
@endsection
