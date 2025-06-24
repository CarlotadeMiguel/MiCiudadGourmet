<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Listar todas las reseñas (público)
     */
    public function index(): JsonResponse
    {
        $reviews = Review::with(['user', 'restaurant'])->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Mostrar una reseña concreta (público)
     */
    public function show(Review $review): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'restaurant'])
        ]);
    }

    /**
     * Crear una reseña (protegido)
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Evitar duplicados: un usuario solo puede reseñar un restaurante una vez
        $exists = Review::where('user_id', Auth::id())
            ->where('restaurant_id', $validated['restaurant_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has reseñado este restaurante.'
            ], 409);
        }

        $review = Review::create([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'restaurant_id' => $validated['restaurant_id'],
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'restaurant'])
        ], 201);
    }

    /**
     * Actualizar una reseña (protegido)
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $validated = $request->validated();
        $review->update($validated);

        return response()->json([
            'success' => true,
            'data' => $review->fresh(['user', 'restaurant'])
        ]);
    }

    /**
     * Eliminar una reseña (protegido)
     */
    public function destroy(Review $review): JsonResponse
    {
        // Solo el autor puede eliminar su reseña (ya cubierto por la política o FormRequest)
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reseña eliminada correctamente'
        ]);
    }
}
