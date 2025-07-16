@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg rounded-3">
            <div class="card-header bg-primary text-white py-4">
                <h2 class="h4 text-center mb-0">Únete a Mi Ciudad Gourmet</h2>
            </div>
            
            <div class="card-body p-5">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label for="name" class="form-label visually-hidden">Nombre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-user fa-fw"></i>
                            </span>
                            <input id="name" type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   name="name" 
                                   placeholder="Nombre completo"
                                   required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

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
                                   required>
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
                                   placeholder="Contraseña (mínimo 8 caracteres)"
                                   required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirmar contraseña -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label visually-hidden">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-check-circle fa-fw"></i>
                            </span>
                            <input id="password_confirmation" type="password" 
                                   class="form-control form-control-lg" 
                                   name="password_confirmation" 
                                   placeholder="Confirmar contraseña"
                                   required>
                        </div>
                    </div>

                    <!-- Términos -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label text-secondary" for="terms">
                                Acepto los <a href="#" class="text-primary text-decoration-none">términos y condiciones</a>
                            </label>
                        </div>
                    </div>

                    <!-- Botón de registro -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-2">
                        <i class="fas fa-user-plus me-2"></i>Crear cuenta
                    </button>
                </form>
            </div>

            <div class="card-footer bg-transparent py-3 text-center">
                <span class="text-secondary">¿Ya tienes cuenta? </span>
                <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">
                    Inicia sesión aquí
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
