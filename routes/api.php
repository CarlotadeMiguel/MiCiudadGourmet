<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\AuthController;

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

/**
 * Rutas protegidas (requieren autenticación)
 */
Route::middleware('auth:sanctum')->group(function() {
    // CRUD completo de restaurantes excepto index/show (ya públicos)
    Route::post('restaurants', [RestaurantController::class, 'store']);   // Crear restaurante
    Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update']); // Actualizar restaurante
    Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy']); // Eliminar restaurante

});

