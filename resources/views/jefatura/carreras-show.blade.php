@extends('layouts.app')

@section('title', 'Materias de ' . $carrera->nombre)

@section('content')

    @php
        $anioRecordado = old('anio_id', $ultimoAnioId);
        $hayFiltro = $busqueda !== '' || $anioFiltro;
    @endphp

    <div class="section-header">
        <h1>Materias — {{ $carrera->nombre }}</h1>
        <a href="{{ route('jefatura.carreras.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    {{-- Agregar materia --}}
    <div class="data-table-wrapper" style="padding: 24px; margin-bottom: 20px;">

        <form action="{{ route('jefatura.carreras.materias.store', $carrera) }}" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            @csrf

            <div class="form-group" style="margin-bottom: 0;">
                <label for="anio_id">Año</label>
                <select id="anio_id" name="anio_id" required>
                    <option value="">Seleccionar...</option>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" @selected($anioRecordado == $anio->id)>
                            {{ $anio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label for="nombre">Nombre de la materia</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Base de Datos" required style="max-width: 100%;">
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

    {{-- Buscador --}}
    <div class="filter-bar" style="margin-bottom: 20px;">

        <form method="GET" action="{{ route('jefatura.carreras.show', $carrera) }}" class="filter-form">

            <div class="form-group">
                <label for="buscar">Materia</label>
                <input type="text" id="buscar" name="buscar" value="{{ $busqueda }}" placeholder="Buscar...">
            </div>

            <div class="form-group">
                <label for="filtro_anio">Año</label>
                <select id="filtro_anio" name="anio_id">
                    <option value="">Todos</option>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" @selected($anioFiltro == $anio->id)>
                            {{ $anio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>

            @if($hayFiltro)
                <a href="{{ route('jefatura.carreras.show', $carrera) }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>

    </div>

    {{-- Listado --}}
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
                            <a href="{{ route('jefatura.materias.edit', $materia) }}" class="btn btn-outline btn-sm">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">
                            @if($hayFiltro)
                                No se encontraron materias con ese filtro.
                            @else
                                Todavía no hay materias cargadas para esta carrera.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </thead>
    </div>

@endsection