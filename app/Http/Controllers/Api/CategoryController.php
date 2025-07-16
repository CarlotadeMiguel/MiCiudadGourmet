<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest; // <-- Nuevo Request
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // ... (métodos index y show sin cambios)

    /**
     * Actualizar categoría (protegido)
     * @param UpdateCategoryRequest $request // <-- Cambiado aquí
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Eliminar categoría (protegido)
     */
    public function destroy(Category $category): JsonResponse
    {
        // Verificar si hay restaurantes asociados
        if ($category->restaurants()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una categoría con restaurantes asociados'
            ], 422);
        }

        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada'
        ]);
    }
}
