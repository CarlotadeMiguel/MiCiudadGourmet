<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el autor puede actualizar su reseña
        $review = $this->route('review');
        
        // Verificar que review sea un modelo y no una colección
        if ($review instanceof Review) {
            return $review->user_id === Auth::id();
        }
        
        return false;
    }

    public function rules(): array
    {
        return [
            // rating: requerido, entero entre 1 y 5
            'rating' => 'required|integer|min:1|max:5',
            // comment: opcional, string máximo 1000 caracteres
            'comment' => 'nullable|string|max:1000'
        ];
    }
    
    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'La calificación es obligatoria.',
            'rating.min' => 'La calificación debe ser entre 1 y 5 estrellas.',
            'rating.max' => 'La calificación debe ser entre 1 y 5 estrellas.',
        ];
    }
}
