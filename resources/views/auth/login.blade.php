@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')

<form action="{{ route('login') }}" method="POST" class="auth-form">

    @csrf

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


    <div class="auth-input-group">

        <span class="auth-input-icon">
            🔒︎
        </span>

        <input
            type="password"
            name="password"
            placeholder="Contraseña"
            autocomplete="current-password"
            required
        >

    </div>


    <div class="auth-options">

        <label class="remember-me">

            <input
                type="checkbox"
                name="remember"
                value="1"
            >

            <span>Mantener sesión iniciada</span>

        </label>

    </div>


    <button type="submit" class="auth-button">

        <span>➜</span>

        Iniciar Sesión

    </button>


    <div class="auth-links">

        <a href="#" class="forgot-password">
            ¿Olvidaste tu contraseña?
        </a>

        <p>
            ¿No tenés cuenta?
            <a href="{{ route('register') }}">
                Registrate
            </a>
        </p>

    </div>

</form>

@endsection