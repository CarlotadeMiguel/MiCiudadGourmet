<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    // Mostrar listado de restaurantes
    public function index()
    {
        $restaurants = Restaurant::with(['categories', 'photos', 'reviews'])->get();
        return view('restaurants.index', compact('restaurants'));
    }

    // Mostrar detalles de un restaurante
    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['categories', 'photos', 'reviews']);
        return view('restaurants.show', compact('restaurant'));
    }

    // Formulario de creación
    public function create()
    {
        $categories = Category::all();
        return view('restaurants.form', [
            'categories' => $categories,
            'restaurant' => null
        ]);
    }

    // Guardar restaurante nuevo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'photo' => 'nullable|image|max:2048' // Nueva validación
        ]);
    
        $restaurant = Restaurant::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
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
        $categories = Category::all();
        return view('restaurants.form', compact('restaurant', 'categories'));
    }

    // Actualizar restaurante
    public function update(Request $request, Restaurant $restaurant)
{
    if (Auth::id() !== $restaurant->user_id) {
        abort(403, 'No autorizado');
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string',
        'phone' => 'nullable|string|max:20',
        'category_ids' => 'required|array|min:1',
        'category_ids.*' => 'exists:categories,id',
        'photo' => 'nullable|image|max:2048'
    ]);

    $restaurant->update($validated);
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
