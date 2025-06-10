<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRestaurantRequest extends FormRequest
{
    /**
     * Solo usuarios autenticados pueden crear restaurantes.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Reglas de validación para crear restaurantes.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'category_ids.required' => 'Debe seleccionar al menos una categoría.',
            'category_ids.*.exists' => 'Una o más categorías seleccionadas no existen.',
        ];
    }
}
