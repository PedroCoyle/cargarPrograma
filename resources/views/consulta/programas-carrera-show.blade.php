@extends('layouts.app')

@section('title', 'Programas — ' . $carrera->nombre)

@section('content')

    <div class="section-header">
        <h1>Programas — {{ $carrera->nombre }}</h1>
        <a href="{{ route('consulta.programas.index') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('consulta.programas.carrera', $carrera) }}" class="filter-form">

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

            <button type="submit" class="btn btn-primary">Filtrar</button>

            @if($busqueda)
                <a href="{{ route('consulta.programas.carrera', $carrera) }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>
    </div>

    @forelse($grupos as $grupo)

        <div class="programa-group">

            <div class="programa-group-header">
                <div>
                    <strong>{{ $grupo['profesor']->nombre ?? $grupo['profesor']->user->email }}</strong>
                    <span class="programa-group-materia">
                        {{ $grupo['materia']->anio->nombre }} — {{ $grupo['materia']->nombre }}
                    </span>
                </div>

                <div style="display: flex; gap: 8px; align-items: center;">
                    @if($grupo['completo'])
                        <span class="badge badge-profesor">Completo</span>
                    @else
                        <span class="badge badge-sin-rol">{{ $grupo['programas']->count() }}/3</span>
                    @endif

                    @if($puedeHistorial)
                        <a href="{{ route('consulta.programas.historial', [$grupo['profesor'], $grupo['materia']]) }}" class="btn btn-outline btn-sm">
                            Historial
                        </a>
                    @endif
                </div>
            </div>

            <div class="programa-slots">
                @for($i = 0; $i < 3; $i++)
                    @php $programa = $grupo['programas']->get($i); @endphp

                    <div class="programa-slot {{ $programa ? 'programa-slot-filled' : 'programa-slot-empty' }}">
                        <span class="programa-slot-label">Archivo {{ $i + 1 }}</span>

                        @if($programa)
                            <span class="programa-slot-filename">{{ basename($programa->archivo) }}</span>
                            <span class="programa-slot-date">{{ $programa->fecha_subida?->format('d/m/Y') }}</span>

                            <div class="programa-slot-actions">
                                <a href="{{ asset('storage/' . $programa->archivo) }}" target="_blank" class="btn btn-outline btn-sm">
                                    Ver
                                </a>
                            </div>
                        @else
                            <span class="programa-slot-missing">Sin subir</span>
                        @endif
                    </div>
                @endfor
            </div>

        </div>

    @empty
        <div class="data-table-wrapper">
            <p class="empty" style="padding: 24px;">No se encontraron programas.</p>
        </div>
    @endforelse

@endsection