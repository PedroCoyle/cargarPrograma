@extends('layouts.app')

@section('title', 'Asignaciones')

@section('content')

    <div class="section-header">
        <h1>Asignaciones</h1>
    </div>

    <div class="data-table-wrapper">

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Asignar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesores as $profesor)
                    <tr>
                        <td>{{ $profesor->nombre ?? '—' }}</td>
                        <td>{{ $profesor->user->email }}</td>
                        <td>
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_PROFESOR))
                                <span class="badge badge-profesor">Profesor</span>
                            @endif
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_PRECEPTOR))
                                <span class="badge badge-preceptor">Preceptor</span>
                            @endif
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_EMTP))
                                <span class="badge badge-emtp">EMTP</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_PROFESOR))
                                <a href="{{ route('admin.profesores.materias', $profesor) }}" class="btn btn-outline btn-sm">
                                    Materias
                                </a>
                            @endif
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_PRECEPTOR))
                                <a href="{{ route('admin.profesores.cursos', $profesor) }}" class="btn btn-outline btn-sm">
                                    Cursos
                                </a>
                            @endif
                            @if($profesor->tieneRol(\App\Models\Profesor::ROL_EMTP))
                                <a href="{{ route('admin.profesores.carreras', $profesor) }}" class="btn btn-outline btn-sm">
                                    Carreras
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">Todavía no hay usuarios con roles que admitan asignaciones.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

@endsection