<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Almacena una nueva reseña en la base de datos.
     */
    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $restaurantId = $validated['restaurant_id'];
        $userId = Auth::id();
        
        // Crear la reseña (las validaciones ya se manejan en el FormRequest)
        Review::create([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'restaurant_id' => $restaurantId,
            'user_id' => $userId
        ]);

        return redirect()->route('restaurants.show', $restaurantId)
            ->with('success', 'Reseña publicada correctamente.');
    }
    
    /**
     * Actualiza una reseña existente.
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        // La autorización ya se maneja en el FormRequest
        $validated = $request->validated();
        
        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);
        
        return redirect()->route('restaurants.show', $review->restaurant_id)
            ->with('success', 'Reseña actualizada correctamente.');
    }
}