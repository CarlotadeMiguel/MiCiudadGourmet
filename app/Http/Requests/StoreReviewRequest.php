<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden crear reseñas
        return Auth::check();
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
