@extends('layouts.app')

@section('title', 'Asignar materias')

@section('content')

    <div class="section-header">
        <h1>Materias de {{ $profesor->nombre ?? $profesor->user->email }}</h1>
        <a href="{{ route('admin.profesores.materias.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    {{-- Materias ya asignadas --}}
    <div class="data-table-wrapper" style="margin-bottom: 24px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Carrera</th>
                    <th>Año</th>
                    <th>Materia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asignadas as $materia)
                    <tr>
                        <td>{{ $materia->carrera->nombre }}</td>
                        <td>{{ $materia->anio->nombre }}</td>
                        <td>{{ $materia->nombre }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">Todavía no tiene materias asignadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Filtro --}}
    <div class="filter-bar">

        <form method="GET" action="{{ route('admin.profesores.materias', $profesor) }}" class="filter-form">

            <div class="form-group">
                <label for="carrera_id">Carrera</label>
                <select name="carrera_id" id="carrera_id" onchange="this.form.submit()">
                    <option value="">Seleccionar...</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->id }}" @selected($carreraId == $carrera->id)>
                            {{ $carrera->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="anio_id">Año</label>
                <select name="anio_id" id="anio_id" onchange="this.form.submit()">
                    <option value="">Seleccionar...</option>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" @selected($anioId == $anio->id)>
                            {{ $anio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

        </form>

    </div>

    {{-- Tabla de materias del filtro --}}
    @if($carreraId && $anioId)

        <form method="POST" action="{{ route('admin.profesores.materias.update', $profesor) }}">
            @csrf

            <input type="hidden" name="carrera_id" value="{{ $carreraId }}">
            <input type="hidden" name="anio_id" value="{{ $anioId }}">

            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 48px;">
                                <input type="checkbox" id="check-all" title="Tildar todas">
                            </th>
                            <th>Materia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materiasFiltradas as $materia)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="materias[]"
                                        value="{{ $materia->id }}"
                                        class="materia-row-check"
                                        @checked(in_array($materia->id, $asignadasIds))
                                    >
                                </td>
                                <td>{{ $materia->nombre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="empty">No hay materias cargadas para esa carrera y año.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($materiasFiltradas->isNotEmpty())
                <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Guardar</button>
            @endif

        </form>

        <script>
            document.getElementById('check-all').addEventListener('change', function () {
                document.querySelectorAll('.materia-row-check').forEach(function (checkbox) {
                    checkbox.checked = this.checked;
                }.bind(this));
            });
        </script>

    @else
        <p style="margin-top: 16px; color: #64748b;">Elegí una carrera y un año para ver las materias disponibles.</p>
    @endif

@endsection