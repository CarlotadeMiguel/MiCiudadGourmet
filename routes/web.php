<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AuthController;

// Página principal: listado de restaurantes (pública)
Route::get('/', [RestaurantController::class, 'index'])->name('home');

// Rutas públicas de restaurantes (ver listado y detalle)
Route::resource('restaurants', RestaurantController::class)
    ->only(['index', 'show']);

// Rutas protegidas de restaurantes (crear, editar, actualizar, eliminar)
Route::middleware('auth')->group(function () {
    Route::resource('restaurants', RestaurantController::class)
        ->except(['index', 'show']);
});

// Autenticación: solo para invitados
Route::middleware('guest')->group(function() {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// Logout: solo para autenticados
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
