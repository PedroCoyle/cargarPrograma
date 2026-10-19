@extends('layouts.app')

@section('title', 'Editar perfil')

@section('content')

    <div class="section-header">
        <h1>Editar perfil — {{ $perfil->nombre }}</h1>
        <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        <p class="confirm-intro">
            Cuenta:
            @if($perfil->tieneCuenta())
                <strong>{{ $perfil->user->email }}</strong>
            @else
                <span class="badge badge-pendiente">Esperando registro</span>
                <span class="form-help">La cuenta se asocia desde "Usuarios y roles".</span>
            @endif
        </p>

        <form action="{{ route('admin.perfiles.update', $perfil) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $perfil->nombre) }}" required>
            </div>

            <div class="roles-inline">
                @include('admin.partials.roles-checkboxes', ['seleccionados' => old('roles', $perfil->roles_ids)])
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            @error('roles')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection
