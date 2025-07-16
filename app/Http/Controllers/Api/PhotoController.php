<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Models\Photo;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PhotoController extends Controller
{
    /**
     * Listar fotos (público)
     */
    public function index(Request $request): JsonResponse
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
     * Subir una nueva foto (protegido)
     */
    public function store(StorePhotoRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verificar que la entidad existe
        $modelClass = $validated['imageable_type'];
        if (!$modelClass::find($validated['imageable_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'La entidad especificada no existe'
            ], 404);
        }

        $photo = Photo::create($validated);

        return response()->json([
            'success' => true,
            'data' => $photo->load('imageable')
        ], 201);
    }

    /**
     * Mostrar una foto específica (público)
     */
    public function show($id): JsonResponse
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
     * Actualizar una foto (protegido)
     */
    public function update(UpdatePhotoRequest $request, Photo $photo): JsonResponse
    {
        $validated = $request->validated();

        $photo->update($validated);

        return response()->json([
            'success' => true,
            'data' => $photo->load('imageable')
        ]);
    }

    /**
     * Eliminar una foto (protegido)
     */
    public function destroy(Photo $photo): JsonResponse
    {
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada'
        ]);
    }
}
