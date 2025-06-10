<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    /**
     * Listar todos los restaurantes (público)
     */
    public function index()
    {
        // Cargar restaurantes con categorías, fotos y reseñas
        $restaurants = Restaurant::with(['categories', 'photos', 'reviews'])->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Mostrar un restaurante concreto (público)
     */
    public function show($id)
    {
        $restaurant = Restaurant::with(['categories', 'photos', 'reviews'])->find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }

    /**
     * Crear un restaurante (protegido)
     */
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:30',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id'
        ]);

        // Crear restaurante asociado al usuario autenticado
        $restaurant = Restaurant::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'user_id' => Auth::id(),
        ]);

        // Asociar categorías
        $restaurant->categories()->attach($validated['category_ids']);

        // Puedes asociar fotos aquí si las recibes en la petición

        return response()->json([
            'success' => true,
            'data' => $restaurant->load(['categories', 'photos', 'reviews'])
        ], 201);
    }

    /**
     * Actualizar un restaurante (protegido)
     */
    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        // Opcional: Permitir solo al dueño editar
        if ($restaurant->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'phone' => 'nullable|string|max:30',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $restaurant->update($validated);

        // Actualizar categorías si se envían
        if (isset($validated['category_ids'])) {
            $restaurant->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant->load(['categories', 'photos', 'reviews'])
        ]);
    }

    /**
     * Eliminar un restaurante (protegido)
     */
    public function destroy($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no encontrado'
            ], 404);
        }

        // Opcional: Solo el dueño puede borrar
        if ($restaurant->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurante eliminado'
        ]);
    }
}
