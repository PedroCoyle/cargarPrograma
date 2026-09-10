@extends('layouts.app')

@section('title', 'Asignar materias')

@section('content')

    <div class="section-header">
        <h1>Asignar materias</h1>
    </div>

    <div class="data-table-wrapper">

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesores as $profesor)
                    <tr>
                        <td>{{ $profesor->nombre ?? '—' }}</td>
                        <td>{{ $profesor->user->email }}</td>
                        <td>
                            <a href="{{ route('admin.profesores.materias', $profesor) }}" class="btn btn-outline btn-sm">
                                Asignar materias
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">Todavía no hay profesores con ese rol.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

@endsection