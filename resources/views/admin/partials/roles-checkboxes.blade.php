@php
    $todas = [
        \App\Models\Profesor::ROL_PROFESOR => 'Profesor',
        \App\Models\Profesor::ROL_PRECEPTOR => 'Preceptor',
        \App\Models\Profesor::ROL_EMTP => 'EMTP',
        \App\Models\Profesor::ROL_JEFE => 'Jefe de Area',
        \App\Models\Profesor::ROL_DIRECTIVO => 'Directivo',
        \App\Models\Profesor::ROL_ADMIN => 'Admin',
    ];

    $disponibles = $rolesDisponibles ?? array_keys($todas);
@endphp

<div class="form-group">
    <label>Roles</label>

    @foreach($todas as $valor => $texto)
        @if(in_array($valor, $disponibles))
            <label class="materia-checkbox">
                <input
                    type="checkbox"
                    name="roles[]"
                    value="{{ $valor }}"
                    @checked(in_array($valor, $seleccionados))
                >
                {{ $texto }}
            </label>
        @endif
    @endforeach
</div>