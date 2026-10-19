@extends('layouts.app')

@section('title', 'Eliminar perfil')

@section('content')

    <div class="section-header">
        <h1>Eliminar perfil — {{ $perfil->nombre }}</h1>
        <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        @php $afectados = collect($resumen)->where('count', '>', 0); @endphp

        @if($afectados->isNotEmpty())
            <p class="confirm-intro">Al eliminar el perfil se borra de forma permanente:</p>

            <ul class="confirm-lista">
                @foreach($afectados as $item)
                    <li><strong>{{ $item['count'] }}</strong> {{ $item['label'] }}</li>
                @endforeach
            </ul>
        @endif

        <p class="confirm-intro">Esta acción no se puede deshacer.</p>

        <form method="POST" action="{{ route('admin.perfiles.destroy', $perfil) }}">
            @csrf
            @method('DELETE')

            @if($perfil->tieneCuenta())
                <label class="opcion-eliminar">
                    <input type="radio" name="modo" value="solo_perfil" checked>
                    <span>
                        <strong>Eliminar solo el perfil</strong>
                        La cuenta {{ $perfil->user->email }} se conserva, sin roles. La persona puede volver a
                        entrar y esperar un nuevo perfil.
                    </span>
                </label>

                <label class="opcion-eliminar">
                    <input type="radio" name="modo" value="perfil_y_cuenta">
                    <span>
                        <strong>Eliminar perfil y cuenta</strong>
                        La persona deja de poder ingresar.
                    </span>
                </label>
            @endif

            @if($requiereConfirmacion)
                <div class="form-group">
                    <label for="confirmacion">Para confirmar, escribí ELIMINAR</label>
                    <input type="text" id="confirmacion" name="confirmacion" autocomplete="off">
                </div>

                @error('confirmacion')
                    <div class="auth-errors">{{ $message }}</div>
                @enderror
            @endif

            <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
            <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection