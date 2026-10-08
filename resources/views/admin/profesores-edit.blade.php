@extends('layouts.app')

@section('title', 'Editar profesor')

@section('content')

    <div class="section-header">
        <h1>Editar roles de {{ $profesor->user->email }}</h1>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        <form action="{{ route('admin.profesores.update', $profesor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $profesor->nombre) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>Roles</label>

                <label class="materia-checkbox">
                    <input type="checkbox" name="roles[]" value="{{ \App\Models\Profesor::ROL_PROFESOR }}"
                        @checked(in_array(\App\Models\Profesor::ROL_PROFESOR, old('roles', $profesor->roles_ids)))>
                    Profesor
                </label>

                <label class="materia-checkbox">
                    <input type="checkbox" name="roles[]" value="{{ \App\Models\Profesor::ROL_PRECEPTOR }}"
                        @checked(in_array(\App\Models\Profesor::ROL_PRECEPTOR, old('roles', $profesor->roles_ids)))>
                    Preceptor
                </label>

                <label class="materia-checkbox">
                    <input type="checkbox" name="roles[]" value="{{ \App\Models\Profesor::ROL_ADMIN }}"
                        @checked(in_array(\App\Models\Profesor::ROL_ADMIN, old('roles', $profesor->roles_ids)))>
                    Admin
                </label>

                <label class="materia-checkbox">
                    <input type="checkbox" name="roles[]" value="{{ \App\Models\Profesor::ROL_EMTP }}"
                        @checked(in_array(\App\Models\Profesor::ROL_EMTP, old('roles', $profesor->roles_ids)))>
                    EMTP
                </label>

                <label class="materia-checkbox">
                    <input type="checkbox" name="roles[]" value="{{ \App\Models\Profesor::ROL_DIRECTIVO }}"
                        @checked(in_array(\App\Models\Profesor::ROL_DIRECTIVO, old('roles', $profesor->roles_ids)))>
                    Directivo
                </label>
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            @error('roles')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary" style="margin-top: 12px;">Guardar</button>
            <a href="{{ route('admin.profesores') }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection