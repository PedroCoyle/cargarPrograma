@extends('layouts.app')

@section('title', 'Editar materia')

@section('content')

    <div class="section-header">
        <h1>Editar materia — {{ $materia->carrera->nombre }}</h1>
        <a href="{{ route('jefatura.carreras.show', $materia->carrera_id) }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        <form action="{{ route('jefatura.materias.update', $materia) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="anio_id">Año</label>
                <select id="anio_id" name="anio_id" required>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" @selected(old('anio_id', $materia->anio_id) == $anio->id)>
                            {{ $anio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre de la materia</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $materia->nombre) }}" required>
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            @error('anio_id')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('jefatura.carreras.show', $materia->carrera_id) }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection