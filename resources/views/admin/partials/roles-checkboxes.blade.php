@php
    $opciones = [
        \App\Models\Profesor::ROL_PROFESOR => 'Profesor',
        \App\Models\Profesor::ROL_PRECEPTOR => 'Preceptor',
        \App\Models\Profesor::ROL_EMTP => 'EMTP',
        \App\Models\Profesor::ROL_DIRECTIVO => 'Directivo',
        \App\Models\Profesor::ROL_ADMIN => 'Admin',
    ];
@endphp

<div class="form-group">
    <label>Roles</label>

    @foreach($opciones as $valor => $texto)
        <label class="materia-checkbox">
            <input
                type="checkbox"
                name="roles[]"
                value="{{ $valor }}"
                @checked(in_array($valor, $seleccionados))
            >
            {{ $texto }}
        </label>
    @endforeach
</div>