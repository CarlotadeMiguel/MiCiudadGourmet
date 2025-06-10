<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se definen las rutas de la API para MiCiudadGourmet.
| Las rutas públicas no requieren autenticación.
| Las rutas protegidas requieren autenticación con Sanctum.
|
*/

/**
 * Rutas de autenticación (registro, login, logout)
 */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

/**
 * Rutas públicas (accesibles sin autenticación)
 */
Route::get('restaurants', [RestaurantController::class, 'index']);    // Listar restaurantes
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']); // Ver detalle restaurante

Route::get('categories', [CategoryController::class, 'index']);       // Listar categorías
Route::get('categories/{category}', [CategoryController::class, 'show']); // Ver detalle categoría

Route::get('reviews', [ReviewController::class, 'index']);            // Listar reseñas
Route::get('reviews/{review}', [ReviewController::class, 'show']);    // Ver detalle reseña

Route::get('photos', [PhotoController::class, 'index']);              // Listar fotos
Route::get('photos/{photo}', [PhotoController::class, 'show']);       // Ver detalle foto

/**
 * Rutas protegidas (requieren autenticación)
 */
Route::middleware('auth:sanctum')->group(function() {
    // Restaurantes: crear, actualizar, eliminar
    Route::post('restaurants', [RestaurantController::class, 'store']);   
    Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update']); 
    Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy']); 

    // Categorías: crear, actualizar, eliminar
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // Favoritos: CRUD completo
    Route::apiResource('favorites', FavoriteController::class);

    // Reseñas: crear, actualizar, eliminar
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

    // Fotos: crear, actualizar, eliminar
    Route::post('photos', [PhotoController::class, 'store']);
    Route::put('photos/{photo}', [PhotoController::class, 'update']);
    Route::delete('photos/{photo}', [PhotoController::class, 'destroy']);
});
