<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $photo = Photo::find($this->route('photo'));
        // Solo el dueño del restaurante/review puede actualizar la foto
        if ($photo && $photo->imageable_type === 'App\Models\Restaurant') {
            return $photo->imageable && $photo->imageable->user_id === Auth::id();
        }
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'url' => 'sometimes|required|url|max:255'
        ];
    }
}
