@extends('layouts.app')

@section('title', 'Asociar perfil')

@section('content')

    @php
        $nombresRol = [
            \App\Models\Profesor::ROL_PROFESOR => 'Profesor',
            \App\Models\Profesor::ROL_PRECEPTOR => 'Preceptor',
            \App\Models\Profesor::ROL_ADMIN => 'Admin',
            \App\Models\Profesor::ROL_EMTP => 'EMTP',
            \App\Models\Profesor::ROL_DIRECTIVO => 'Directivo',
            \App\Models\Profesor::ROL_JEFE => 'Jefe de área / depto.',
        ];

        $actual = $usuario->profesor;
        $tienePerfil = $actual && $actual->creado_por_admin;
    @endphp

    <div class="section-header">
        <h1>{{ $tienePerfil ? 'Cambiar perfil' : 'Asociar perfil' }} — {{ $usuario->email }}</h1>
        <a href="{{ route('admin.usuarios') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        @if($tienePerfil)
            <p class="confirm-intro">
                Perfil actual: <strong>{{ $actual->nombre }}</strong>.
                Si elegís otro, el actual queda esperando registro, con todas sus asignaciones.
            </p>
        @else
            <p class="confirm-intro">
                Elegí el perfil que corresponde a <strong>{{ $usuario->nombre }}</strong>.
                La persona va a ver sus roles y asignaciones apenas recargue la página.
            </p>
        @endif

        @if($perfiles->isEmpty())
            <p class="confirm-intro">
                No hay perfiles disponibles. Solo aparecen los que todavía no tienen cuenta.
                Podés crear uno en <a href="{{ route('admin.perfiles.index') }}">Perfiles</a>.
            </p>
        @else
            <form action="{{ route('admin.usuarios.associate.store', $usuario) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="perfil_id">Perfil</label>
                    <select id="perfil_id" name="perfil_id" required style="max-width: 480px;">
                        <option value="">Seleccionar...</option>
                        @foreach($perfiles as $perfil)
                            <option value="{{ $perfil->id }}" @selected(old('perfil_id') == $perfil->id)>
                                {{ $perfil->nombre }} —
                                {{ collect($perfil->roles_ids)->map(fn ($id) => $nombresRol[$id] ?? '')->filter()->implode(', ') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('perfil_id')
                    <div class="auth-errors">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary">
                    {{ $tienePerfil ? 'Cambiar' : 'Asociar' }}
                </button>
                <a href="{{ route('admin.usuarios') }}" class="btn btn-outline">Cancelar</a>

            </form>
        @endif

    </div>

@endsection