<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Listar todas las reseñas
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'restaurant']);

        // Filtrar por restaurante si se especifica
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Filtrar por usuario si se especifica
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Crear una nueva reseña
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        // Verificar si el usuario ya reseñó este restaurante
        $existingReview = Review::where('user_id', Auth::id())
            ->where('restaurant_id', $validated['restaurant_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has reseñado este restaurante'
            ], 409);
        }

        $review = Review::create([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'restaurant_id' => $validated['restaurant_id'],
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'restaurant'])
        ], 201);
    }

    /**
     * Mostrar una reseña específica
     */
    public function show($id)
    {
        $review = Review::with(['user', 'restaurant'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Reseña no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Actualizar una reseña
     */
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Reseña no encontrada'
            ], 404);
        }

        // Solo el autor puede actualizar su reseña
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'data' => $review->load(['user', 'restaurant'])
        ]);
    }

    /**
     * Eliminar una reseña
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Reseña no encontrada'
            ], 404);
        }

        // Solo el autor puede eliminar su reseña
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reseña eliminada'
        ]);
    }
}
