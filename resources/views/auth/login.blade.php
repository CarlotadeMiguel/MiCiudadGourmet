@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg rounded-3">
            <div class="card-header bg-primary text-white py-4">
                <h2 class="h4 text-center mb-0">Bienvenido a Mi Ciudad Gourmet</h2>
            </div>
            
            <div class="card-body p-5">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label visually-hidden">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-envelope fa-fw"></i>
                            </span>
                            <input id="email" type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   name="email" 
                                   placeholder="Correo electrónico"
                                   required autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-4">
                        <label for="password" class="form-label visually-hidden">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-lock fa-fw"></i>
                            </span>
                            <input id="password" type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   name="password" 
                                   placeholder="Contraseña"
                                   required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Recordar sesión y olvidé contraseña -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label text-secondary" for="remember">
                                Recordar sesión
                            </label>
                        </div>
                    </div>

                    <!-- Botón de login -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                    </button>

                    <!-- Separador -->
                    <div class="text-center my-4">
                        <span class="text-secondary">O ingresa con</span>
                    </div>

                    <!-- Social login -->
                    <div class="d-grid gap-3">
                        <a href="#" class="btn btn-outline-primary btn-lg py-2">
                            <i class="fab fa-google me-2"></i>Google
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-lg py-2">
                            <i class="fab fa-facebook me-2"></i>Facebook
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-transparent py-3 text-center">
                <span class="text-secondary">¿No tienes cuenta? </span>
                <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-bold">
                    Regístrate aquí
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
