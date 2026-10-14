@extends('layouts.app')

@section('title', $titulo)

@section('content')

    <div class="section-header">
        <h1>{{ $titulo }} — {{ $nombre }}</h1>
        <a href="{{ $volver }}" class="btn btn-outline">Volver</a>
    </div>

    <div class="data-table-wrapper" style="padding: 24px;">

        @php $afectados = collect($resumen)->where('count', '>', 0); @endphp

        @if($afectados->isNotEmpty())
            <p class="confirm-intro">Al eliminar, también se va a borrar de forma permanente:</p>

            <ul class="confirm-lista">
                @foreach($afectados as $item)
                    <li><strong>{{ $item['count'] }}</strong> {{ $item['label'] }}</li>
                @endforeach
            </ul>
        @else
            <p class="confirm-intro">No hay datos asociados.</p>
        @endif

        <p class="confirm-intro">Esta acción no se puede deshacer.</p>

        <form method="POST" action="{{ $action }}">
            @csrf
            @method('DELETE')

            @if($requiereConfirmacion)
                <div class="form-group">
                    <label for="confirmacion">Para confirmar, escribí ELIMINAR</label>
                    <input type="text" id="confirmacion" name="confirmacion" autocomplete="off" required>
                </div>

                @error('confirmacion')
                    <div class="auth-errors">{{ $message }}</div>
                @enderror
            @endif

            <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
            <a href="{{ $volver }}" class="btn btn-outline">Cancelar</a>

        </form>

    </div>

@endsection