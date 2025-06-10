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
        $review = Review::find($this->route('review'));
        return $review && $review->user_id === Auth::id();
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
