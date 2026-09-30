@extends('layouts.app')

@section('title', 'Materias de ' . $carrera->nombre)

@section('content')

    <div class="section-header">
        <h1>Materias — {{ $carrera->nombre }}</h1>
        <a href="{{ route('admin.carreras.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <div class="data-table-wrapper" style="padding: 24px; margin-bottom: 24px;">

        <form action="{{ route('admin.carreras.materias.store', $carrera) }}" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            @csrf

            <div class="form-group" style="margin-bottom: 0;">
                <label for="anio_id">Año</label>
                <select id="anio_id" name="anio_id" required>
                    <option value="">Seleccionar...</option>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}">{{ $anio->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label for="nombre">Nombre de la materia</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Base de Datos" required style="max-width: 100%;">
            </div>

            <button type="submit" class="btn btn-primary">Agregar</button>

        </form>

        @error('nombre')
            <div class="auth-errors" style="margin-top: 12px;">{{ $message }}</div>
        @enderror

        @error('anio_id')
            <div class="auth-errors" style="margin-top: 12px;">{{ $message }}</div>
        @enderror

    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Materia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materias as $materia)
                    <tr>
                        <td>{{ $materia->anio->nombre }}</td>
                        <td>{{ $materia->nombre }}</td>
                        <td class="actions-cell">
                            <form
                                action="{{ route('admin.materias.destroy', $materia) }}"
                                method="POST"
                                onsubmit="return confirm('¿Eliminar esta materia? Se van a borrar también las asignaciones a profesores y los programas subidos para ella. Esta acción no se puede deshacer.');"
                                style="display: inline;"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">Todavía no hay materias cargadas para esta carrera.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection