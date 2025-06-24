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
            'name' => 'required|string|max:255|unique:restaurants,name',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'description' => 'nullable|string',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'photo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif|dimensions:min_width=200,min_height=200',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe un restaurante con este nombre.',
            'name.required' => 'El nombre del restaurante es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'category_ids.required' => 'Debe seleccionar al menos una categoría.',
            'category_ids.min' => 'Debe seleccionar al menos una categoría.',
            'category_ids.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.max' => 'La imagen no puede ser mayor a 2MB.',
            'photo.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg o gif.',
            'photo.dimensions' => 'La imagen debe tener al menos 200x200 píxeles.',
        ];
    }
}
