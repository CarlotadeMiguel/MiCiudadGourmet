<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Photo;
use App\Models\Restaurant;

class PhotoController extends Controller
{
    /**
     * Listar fotos
     */
    public function index(Request $request)
    {
        $query = Photo::with('imageable');

        // Filtrar por tipo de entidad
        if ($request->has('imageable_type')) {
            $query->where('imageable_type', $request->imageable_type);
        }

        // Filtrar por ID de entidad
        if ($request->has('imageable_id')) {
            $query->where('imageable_id', $request->imageable_id);
        }

        $photos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $photos
        ]);
    }

    /**
     * Subir una nueva foto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'imageable_type' => 'required|in:App\Models\Restaurant,App\Models\Review',
            'imageable_id' => 'required|integer'
        ]);

        // Verificar que la entidad existe
        $modelClass = $validated['imageable_type'];
        if (!$modelClass::find($validated['imageable_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'La entidad especificada no existe'
            ], 404);
        }

        // Si es un restaurante, verificar que pertenece al usuario
        if ($validated['imageable_type'] === 'App\Models\Restaurant') {
            $restaurant = Restaurant::find($validated['imageable_id']);
            if ($restaurant->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }
        }

        $photo = Photo::create($validated);

        return response()->json([
            'success' => true,
            'data' => $photo->load('imageable')
        ], 201);
    }

    /**
     * Mostrar una foto específica
     */
    public function show($id)
    {
        $photo = Photo::with('imageable')->find($id);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Foto no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $photo
        ]);
    }

    /**
     * Actualizar una foto
     */
    public function update(Request $request, $id)
    {
        $photo = Photo::find($id);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Foto no encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'url' => 'sometimes|required|url'
        ]);

        $photo->update($validated);

        return response()->json([
            'success' => true,
            'data' => $photo->load('imageable')
        ]);
    }

    /**
     * Eliminar una foto
     */
    public function destroy($id)
    {
        $photo = Photo::find($id);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Foto no encontrada'
            ], 404);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada'
        ]);
    }
}
