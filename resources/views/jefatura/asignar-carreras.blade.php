@extends('layouts.app')

@section('title', 'Carreras del EMTP')

@section('content')

    <div class="section-header">
        <h1>Carreras de {{ $profesor->nombre ?? $profesor->user->email }}</h1>
        <a href="{{ route('jefatura.perfiles.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <p class="form-help" style="margin-bottom: 12px;">Solo ves y modificás las carreras de tu área.</p>

    <form method="POST" action="{{ route('jefatura.perfiles.carreras.update', $profesor) }}">
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
                            <td colspan="2" class="empty">Todavía no tenés carreras asignadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Guardar</button>

    </form>

@endsection