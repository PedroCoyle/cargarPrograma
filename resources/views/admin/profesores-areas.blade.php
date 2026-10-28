@extends('layouts.app')

@section('title', 'Área del jefe')

@section('content')

    <div class="section-header">
        <h1>Área de {{ $profesor->nombre ?? $profesor->user->email }}</h1>
        <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.profesores.areas.update', $profesor) }}">
        @csrf

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 48px;"></th>
                        <th>Carrera</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carreras as $carrera)
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="carreras[]"
                                    value="{{ $carrera->id }}"
                                    @checked(in_array($carrera->id, $asignadasIds))
                                >
                            </td>
                            <td>{{ $carrera->nombre }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">No hay carreras cargadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Guardar</button>

    </form>

@endsection