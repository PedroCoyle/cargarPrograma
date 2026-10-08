@extends('layouts.app')

@section('title', 'Cursos del preceptor')

@section('content')

    <div class="section-header">
        <h1>Cursos de {{ $profesor->nombre ?? $profesor->user->email }}</h1>
        <a href="{{ route('admin.profesores.materias.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.profesores.cursos.update', $profesor) }}">
        @csrf

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        @foreach($anios as $anio)
                            <th style="text-align: center;">{{ $anio->nombre }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($carreras as $carrera)
                        <tr>
                            <td>{{ $carrera->nombre }}</td>
                            @foreach($anios as $anio)
                                @php $valor = $carrera->id . '-' . $anio->id; @endphp
                                <td style="text-align: center;">
                                    <input
                                        type="checkbox"
                                        name="cursos[]"
                                        value="{{ $valor }}"
                                        @checked(in_array($valor, $asignados))
                                    >
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $anios->count() + 1 }}" class="empty">No hay carreras cargadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Guardar</button>

    </form>

@endsection