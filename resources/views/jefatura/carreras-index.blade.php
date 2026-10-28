@extends('layouts.app')

@section('title', 'Carreras y materias')

@section('content')

    <div class="section-header">
        <h1>Carreras de mi área</h1>
    </div>

    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Carrera</th>
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
                            <a href="{{ route('jefatura.carreras.show', $carrera) }}" class="btn btn-outline btn-sm">
                                Ver materias
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">
                            Todavía no tenés carreras asignadas. Pedile al administrador que te asigne tu área.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection