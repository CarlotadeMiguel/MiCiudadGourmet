<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    // Mostrar listado de restaurantes
    public function index()
    {
        $restaurants = Restaurant::with(['categories', 'photos' => function($query) {
            $query->latest()->take(1);
        }])->withCount('reviews')->withAvg('reviews', 'rating')->get();
        
        return view('restaurants.index', compact('restaurants'));
    }

    // Mostrar detalles de un restaurante
    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['categories', 'photos', 'reviews.user']);
        return view('restaurants.show', compact('restaurant'));
    }

    // Formulario de creación
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('restaurants.form', [
            'categories' => $categories,
            'restaurant' => null
        ]);
    }

    // Guardar restaurante nuevo
    public function store(StoreRestaurantRequest $request)
    {
        $validated = $request->validated();
    
        $restaurant = Restaurant::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'description' => $validated['description'] ?? null,
            'user_id' => Auth::id()
        ]);
        
        $restaurant->categories()->attach($validated['category_ids']);
    
        // Guardar foto si se subió
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('restaurants', 'public');
            $restaurant->photos()->create([
                'url' => '/storage/' . $path,
            ]);
        }
    
        return redirect()->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurante creado correctamente');
    }

    // Formulario de edición
    public function edit(Restaurant $restaurant)
    {
        // Solo el dueño puede editar
        if (Auth::id() !== $restaurant->user_id) {
            abort(403, 'No autorizado');
        }
        
        $restaurant->load('categories');
        $categories = Category::orderBy('name')->get();
        
        return view('restaurants.form', compact('restaurant', 'categories'));
    }

    // Actualizar restaurante
    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant)
    {
        // La autorización ya se maneja en el FormRequest
        $validated = $request->validated();

        $restaurant->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
        ]);
        
        $restaurant->categories()->sync($validated['category_ids']);

        // Guardar nueva foto si se subió
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('restaurants', 'public');
            $restaurant->photos()->create([
                'url' => '/storage/' . $path,
            ]);
        }

        return redirect()->route('restaurants.show', $restaurant)
            ->with('success', 'Restaurante actualizado correctamente');
    }

    // Eliminar restaurante
    public function destroy(Restaurant $restaurant)
    {
        if (Auth::id() !== $restaurant->user_id) {
            abort(403, 'No autorizado');
        }
        $restaurant->delete();
        return redirect()->route('restaurants.index')
            ->with('success', 'Restaurante eliminado correctamente');
    }
}
