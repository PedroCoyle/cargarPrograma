@extends('layouts.app')

@section('title', 'Editar profesor')

@section('content')

    <div class="section-header">
        <h1>Editar rol de {{ $profesor->user->email }}</h1>
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
                <label for="rol_id">Rol</label>
                <select id="rol_id" name="rol_id" required>
                    <option value="">Seleccionar...</option>
                    <option value="{{ \App\Models\Profesor::ROL_PROFESOR }}" @selected(old('rol_id', $profesor->rol_id) == \App\Models\Profesor::ROL_PROFESOR)>
                        Profesor
                    </option>
                    <option value="{{ \App\Models\Profesor::ROL_PRECEPTOR }}" @selected(old('rol_id', $profesor->rol_id) == \App\Models\Profesor::ROL_PRECEPTOR)>
                        Preceptor
                    </option>
                    <option value="{{ \App\Models\Profesor::ROL_ADMIN }}" @selected(old('rol_id', $profesor->rol_id) == \App\Models\Profesor::ROL_ADMIN)>
                        Admin
                    </option>
                </select>
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            @error('rol_id')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.profesores') }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection