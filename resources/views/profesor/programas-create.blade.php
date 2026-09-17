@extends('layouts.app')

@section('title', 'Subir programa')

@section('content')

    <div class="section-header">
        <h1>Subir programa — {{ $materia->nombre }}</h1>
        <a href="{{ route('profesor.programas.index') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        <form action="{{ route('profesor.programas.store', $materia) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="archivo">Archivo (PDF o Word)</label>
                <input type="file" id="archivo" name="archivo" accept=".pdf,.doc,.docx" required>
            </div>

            @error('archivo')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Subir</button>

        </form>

    </div>

@endsection