<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->is_admin; // Solo administradores
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name'
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'El nombre de la categoría ya existe'
        ];
    }
}
