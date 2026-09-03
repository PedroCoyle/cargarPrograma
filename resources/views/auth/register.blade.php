@extends('layouts.auth')

@section('title', 'Crear cuenta')

@section('content')

<form action="{{ route('register') }}" method="POST" class="auth-form">

    @csrf

    {{-- Nombre completo --}}
    <div class="auth-input-group">

        <span class="auth-input-icon">
            👤
        </span>

        <input
            type="text"
            name="nombre"
            value="{{ old('nombre') }}"
            placeholder="Nombre completo"
            autocomplete="name"
            required
        >

    </div>


    {{-- Email institucional --}}
    <div class="auth-input-group">

        <span class="auth-input-icon">
            ✉
        </span>

        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Correo electrónico institucional"
            autocomplete="email"
            required
        >

    </div>


    {{-- Contraseña --}}
    <div class="auth-input-group">

        <span class="auth-input-icon">
            🔒︎
        </span>

        <input
            type="password"
            name="password"
            placeholder="Contraseña"
            autocomplete="new-password"
            required
        >

    </div>


    {{-- Confirmar contraseña --}}
    <div class="auth-input-group">

        <span class="auth-input-icon">
            🔒︎
        </span>

        <input
            type="password"
            name="password_confirmation"
            placeholder="Confirmar contraseña"
            autocomplete="new-password"
            required
        >

    </div>


    {{-- Botón --}}
    <button type="submit" class="auth-button">

        <span>✓</span>

        Crear Cuenta

    </button>


    {{-- Volver al login --}}
    <div class="auth-links">

        <p>
            ¿Ya tenés una cuenta?

            <a href="{{ route('login') }}">
                Iniciar sesión
            </a>
        </p>

    </div>

</form>

@endsection