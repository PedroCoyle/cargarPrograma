@extends('layouts.app')

@section('title', 'Editar carrera')

@section('content')

    <div class="section-header">
        <h1>Editar carrera</h1>
        <a href="{{ route('admin.carreras.index') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        <form action="{{ route('admin.carreras.update', $carrera) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre de la carrera</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $carrera->nombre) }}"
                    required
                    style="max-width: 480px;"
                >
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.carreras.index') }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection
