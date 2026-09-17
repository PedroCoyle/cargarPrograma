@extends('layouts.app')

@section('title', 'Mis programas')

@section('content')

    <div class="section-header">
        <h1>Mis programas</h1>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    @forelse($materias as $materia)

        @php $programasDeLaMateria = $programas->get($materia->id, collect()); @endphp

        <div class="data-table-wrapper" style="margin-bottom: 20px;">

            <div class="section-header" style="padding: 16px 16px 0;">
                <h1 style="font-size: 16px;">
                    {{ $materia->carrera->nombre }} — {{ $materia->anio->nombre }} — {{ $materia->nombre }}
                </h1>
                <a href="{{ route('profesor.programas.create', $materia) }}" class="btn btn-primary btn-sm">
                    Subir programa
                </a>
            </div>

            <table class="data-table" style="margin-top: 12px;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programasDeLaMateria as $programa)
                        <tr>
                            <td>{{ $programa->fecha_subida?->format('d/m/Y H:i') }}</td>
                            <td>{{ basename($programa->archivo) }}</td>
                            <td class="actions-cell">
                                <a href="{{ asset('storage/' . $programa->archivo) }}" target="_blank" class="btn btn-outline btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty">Todavía no subiste ningún programa para esta materia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    @empty
        <p>Todavía no tenés materias asignadas.</p>
    @endforelse

@endsection