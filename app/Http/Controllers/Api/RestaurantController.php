<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    /**
     * Listar todos los restaurantes (público)
     */
    public function index(): JsonResponse
    {
        $restaurants = Restaurant::with(['categories', 'photos', 'reviews'])->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Mostrar un restaurante concreto (público)
     */
    public function show(Restaurant $restaurant): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $restaurant->load(['categories', 'photos', 'reviews'])
        ]);
    }

    /**
     * Crear un restaurante (protegido)
     */
    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $restaurant = Restaurant::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $restaurant->categories()->attach($validated['category_ids']);

        return response()->json([
            'success' => true,
            'data' => $restaurant->load(['categories', 'photos'])
        ], 201);
    }

    /**
     * Actualizar un restaurante (protegido)
     */
    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validated();

        $restaurant->update($validated);

        if (isset($validated['category_ids'])) {
            $restaurant->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant->fresh(['categories', 'photos'])
        ]);
    }

    /**
     * Eliminar un restaurante (protegido)
     */
    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurante eliminado correctamente'
        ]);
    }
}
