@extends('layouts.app')

@section('title', 'Carreras')

@section('content')

    <div class="section-header">
        <h1>Carreras</h1>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <div class="data-table-wrapper" style="padding: 24px; margin-bottom: 24px;">

        <form action="{{ route('admin.carreras.store') }}" method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
            @csrf

            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label for="nombre">Nueva carrera</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Técnico en Informática" required style="max-width: 100%;">
            </div>

            <button type="submit" class="btn btn-primary">Crear</button>

        </form>

        @error('nombre')
            <div class="auth-errors" style="margin-top: 12px;">{{ $message }}</div>
        @enderror

    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Materias cargadas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carreras as $carrera)
                    <tr>
                        <td>{{ $carrera->nombre }}</td>
                        <td>{{ $carrera->materias_count }}</td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.carreras.show', $carrera) }}" class="btn btn-outline btn-sm">
                                Ver materias
                            </a>
                            <a href="{{ route('admin.carreras.edit', $carrera) }}" class="btn btn-outline btn-sm">
                                Editar
                            </a>
                            <a href="{{ route('admin.carreras.delete', $carrera) }}" class="btn btn-danger btn-sm">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">No hay carreras cargadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection