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
        
        // Verificar que el usuario no sea dueño del restaurante
        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        if (Auth::id() === $restaurant->user_id) {
            return back()->with('error', 'No puedes reseñar tu propio restaurante.');
        }
        
        // Evitar duplicados: un usuario solo puede reseñar un restaurante una vez
        $exists = Review::where('user_id', Auth::id())
            ->where('restaurant_id', $validated['restaurant_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya has reseñado este restaurante.');
        }

        // Crear la reseña
        Review::create([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'restaurant_id' => $validated['restaurant_id'],
            'user_id' => Auth::id()
        ]);

        return redirect()->route('restaurants.show', $validated['restaurant_id'])
            ->with('success', 'Reseña publicada correctamente.');
    }
    
    /**
     * Actualiza una reseña existente.
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        // Verificar que el usuario sea el autor de la reseña
        if (Auth::id() !== $review->user_id) {
            return back()->with('error', 'No puedes editar una reseña que no es tuya.');
        }
        
        $validated = $request->validated();
        
        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);
        
        return redirect()->route('restaurants.show', $review->restaurant_id)
            ->with('success', 'Reseña actualizada correctamente.');
    }
}