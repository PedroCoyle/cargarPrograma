@extends('layouts.app')

@section('title', 'Programas — ' . $carrera->nombre)

@section('content')

    <div class="section-header">
        <h1>Programas — {{ $carrera->nombre }}</h1>
        <a href="{{ route('admin.programas.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.programas.carrera', $carrera) }}" class="filter-form">

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
                <a href="{{ route('admin.programas.carrera', $carrera) }}" class="btn btn-outline">Limpiar</a>
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

                    <a href="{{ route('admin.programas.historial', [$grupo['profesor'], $grupo['materia']]) }}" class="btn btn-outline btn-sm">
                        Historial
                    </a>
                </div>
            </div>

            <div class="programa-slots">
                @for($i = 0; $i < 3; $i++)
                    @php $programa = $grupo['programas']->get($i); @endphp

                    <div class="programa-slot {{ $programa ? 'programa-slot-filled' : 'programa-slot-empty' }}">
                        @if($programa)
                            <span class="programa-slot-label">Archivo {{ $i + 1 }}</span>
                            <span class="programa-slot-filename">{{ basename($programa->archivo) }}</span>
                            <span class="programa-slot-date">{{ $programa->fecha_subida?->format('d/m/Y') }}</span>

                            <div class="programa-slot-actions">
                                <a href="{{ asset('storage/' . $programa->archivo) }}" target="_blank" class="btn btn-outline btn-sm">
                                    Ver
                                </a>
                                <form
                                    action="{{ route('admin.programas.destroy', $programa) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Eliminar este archivo?');"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        @else
                            <span class="programa-slot-label">Archivo {{ $i + 1 }}</span>
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