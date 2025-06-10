<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden marcar favoritos
        return Auth::check();
    }

    public function rules(): array
    {
        // restaurant_id: requerido y debe existir en la tabla restaurants
        return [
            'restaurant_id' => 'required|exists:restaurants,id'
        ];
    }

    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Debes indicar el restaurante.',
            'restaurant_id.exists' => 'El restaurante seleccionado no existe.'
        ];
    }
}
