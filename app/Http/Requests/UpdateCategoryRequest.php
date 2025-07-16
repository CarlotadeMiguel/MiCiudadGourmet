<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id;
        
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $categoryId
        ];
    }
}
