<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el dueño de un restaurante puede añadir fotos a ese restaurante
        if ($this->imageable_type === 'App\Models\Restaurant') {
            $restaurant = Restaurant::find($this->imageable_id);
            return $restaurant && $restaurant->user_id === Auth::id();
        }
        // Si es review, puedes personalizar la lógica de autorización si lo necesitas
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'url' => 'required|url|max:255',
            'imageable_type' => 'required|in:App\Models\Restaurant,App\Models\Review',
            'imageable_id' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'imageable_type.in' => 'El tipo debe ser Restaurant o Review',
            'url.url' => 'La URL de la foto no es válida'
        ];
    }
}
