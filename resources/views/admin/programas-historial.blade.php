@extends('layouts.app')

@section('title', 'Historial')

@section('content')

    <div class="section-header">
        <h1>
            Historial — {{ $profesor->nombre ?? $profesor->user->email }} — {{ $materia->nombre }}
        </h1>
        <a href="{{ route('admin.programas.carrera', $materia->carrera_id) }}" class="btn btn-outline">Volver</a>
    </div>

    @forelse($programasPorAnio as $anio => $programas)

        <div class="programa-group">

            <div class="programa-group-header">
                <strong>Año {{ $anio }}</strong>
                <span class="badge {{ $programas->count() >= 3 ? 'badge-profesor' : 'badge-sin-rol' }}">
                    {{ $programas->count() }}/3
                </span>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programas as $programa)
                        <tr>
                            <td>{{ basename($programa->archivo) }}</td>
                            <td>{{ $programa->fecha_subida?->format('d/m/Y H:i') }}</td>
                            <td class="actions-cell">
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    @empty
        <div class="data-table-wrapper">
            <p class="empty" style="padding: 24px;">Este profesor todavía no subió ningún programa para esta materia.</p>
        </div>
    @endforelse

@endsection