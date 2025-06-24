<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Restaurant;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden crear reseñas
        return Auth::check();
    }
    
    /**
     * Configurar validaciones adicionales después de que las reglas base se han validado
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $restaurantId = $this->input('restaurant_id');
            $userId = Auth::id();
            
            // Verificar que el usuario no sea dueño del restaurante
            $restaurant = Restaurant::find($restaurantId);
            if ($restaurant && $userId === $restaurant->user_id) {
                $validator->errors()->add('restaurant_id', 'No puedes reseñar tu propio restaurante.');
            }
            
            // Evitar duplicados: un usuario solo puede reseñar un restaurante una vez
            $exists = Review::where('user_id', $userId)
                ->where('restaurant_id', $restaurantId)
                ->exists();
                
            if ($exists) {
                $validator->errors()->add('restaurant_id', 'Ya has reseñado este restaurante.');
            }
        });
    }

    public function rules(): array
    {
        return [
            // rating: requerido, entero entre 1 y 5
            'rating' => 'required|integer|min:1|max:5',
            // comment: opcional, string máximo 1000 caracteres
            'comment' => 'nullable|string|max:1000',
            // restaurant_id: requerido, debe existir en restaurants
            'restaurant_id' => 'required|exists:restaurants,id'
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'La calificación es obligatoria.',
            'rating.min' => 'La calificación debe ser entre 1 y 5 estrellas.',
            'rating.max' => 'La calificación debe ser entre 1 y 5 estrellas.',
            'restaurant_id.exists' => 'El restaurante seleccionado no existe.'
        ];
    }
}
