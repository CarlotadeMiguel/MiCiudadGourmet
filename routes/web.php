<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AuthController;

// Página principal: listado de restaurantes (pública)
Route::get('/', [RestaurantController::class, 'index'])->name('home');

// RUTAS PROTEGIDAS DEBEN IR ANTES DE LA DE SHOW
Route::middleware('auth')->group(function () {
    Route::get('restaurants/create', [RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('restaurants', [RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('restaurants/{restaurant}/edit', [RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update'])->name('restaurants.update');
    Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');
});

// Rutas públicas de restaurantes (solo index y show)
Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

// Autenticación: solo para invitados
Route::middleware('guest')->group(function() {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// Logout: solo para autenticados
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
