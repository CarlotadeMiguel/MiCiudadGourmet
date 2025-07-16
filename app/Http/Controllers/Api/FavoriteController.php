<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * Listar favoritos del usuario autenticado
     */
    public function index(): JsonResponse
    {
        $favorites = Auth::user()->favorites()->with(['categories', 'photos'])->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Marcar restaurante como favorito
     * @param StoreFavoriteRequest $request
     */
    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = Auth::user();
        $restaurantId = $request->validated()['restaurant_id'];

        // Verificar si ya es favorito
        if ($user->favorites()->where('restaurant_id', $restaurantId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El restaurante ya está en favoritos'
            ], 409);
        }

        $user->favorites()->attach($restaurantId);

        $restaurant = Restaurant::with(['categories', 'photos'])->find($restaurantId);

        return response()->json([
            'success' => true,
            'message' => 'Restaurante añadido a favoritos',
            'data' => $restaurant
        ], 201);
    }

    /**
     * Mostrar un favorito específico
     */
    public function show($restaurantId): JsonResponse
    {
        $user = Auth::user();

        $favorite = $user->favorites()
            ->with(['categories', 'photos', 'reviews'])
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favorito no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $favorite
        ]);
    }

    /**
     * Quitar restaurante de favoritos
     */
    public function destroy($restaurantId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->favorites()->where('restaurant_id', $restaurantId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El restaurante no está en favoritos'
            ], 404);
        }

        $user->favorites()->detach($restaurantId);

        return response()->json([
            'success' => true,
            'message' => 'Restaurante eliminado de favoritos'
        ]);
    }
}
