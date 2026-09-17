@extends('layouts.app')

@section('title', 'Programas')

@section('content')

    <div class="section-header">
        <h1>Programas cargados</h1>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <div class="filter-bar">

        <form method="GET" action="{{ route('admin.programas.index') }}" class="filter-form">

            <div class="form-group">
                <label for="buscar">Profesor (nombre o email)</label>
                <input
                    type="text"
                    id="buscar"
                    name="buscar"
                    value="{{ $busqueda }}"
                    placeholder="Buscar..."
                >
            </div>

            <div class="form-group">
                <label for="carrera_id">Carrera</label>
                <select name="carrera_id" id="carrera_id">
                    <option value="">Todas</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->id }}" @selected($carreraId == $carrera->id)>
                            {{ $carrera->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>

            @if($busqueda || $carreraId)
                <a href="{{ route('admin.programas.index') }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>

    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Profesor</th>
                    <th>Carrera</th>
                    <th>Año</th>
                    <th>Materia</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programas as $programa)
                    <tr>
                        <td>{{ $programa->profesor->nombre ?? $programa->profesor->user->email }}</td>
                        <td>{{ $programa->materia->carrera->nombre }}</td>
                        <td>{{ $programa->materia->anio->nombre }}</td>
                        <td>{{ $programa->materia->nombre }}</td>
                        <td>{{ $programa->fecha_subida?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($programa->ultimo)
                                <span class="badge badge-profesor">Vigente</span>
                            @else
                                <span class="badge badge-sin-rol">Reemplazado</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <a href="{{ asset('storage/' . $programa->archivo) }}" target="_blank" class="btn btn-outline btn-sm">
                                Ver
                            </a>
                            <form
                                action="{{ route('admin.programas.destroy', $programa) }}"
                                method="POST"
                                onsubmit="return confirm('¿Eliminar este programa? Esta acción no se puede deshacer.');"
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
                        <td colspan="7" class="empty">No se encontraron programas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $programas->links() }}
    </div>

@endsection