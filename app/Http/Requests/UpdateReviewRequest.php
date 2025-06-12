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
            // rating: opcional, entero entre 1 y 5
            'rating' => 'sometimes|integer|min:1|max:5',
            // comment: opcional, string máximo 1000 caracteres
            'comment' => 'nullable|string|max:1000'
        ];
    }
}
