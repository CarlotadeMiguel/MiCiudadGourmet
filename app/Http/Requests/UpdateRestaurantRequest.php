<?php

namespace App\Http\Requests;

use App\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRestaurantRequest extends FormRequest
{
    /**
     * Solo el dueño puede actualizar el restaurante.
     */
    public function authorize(): bool
    {
        $restaurant = Restaurant::find($this->route('restaurant'));
        return $restaurant && Auth::id() === $restaurant->user_id;
    }

    /**
     * Reglas de validación para actualizar restaurantes.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'phone' => 'nullable|string|max:20',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ];
    }
}
