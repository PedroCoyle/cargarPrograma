@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @if($sinRol)
        <div class="aviso-card">
            <h2>Tu cuenta está pendiente de activación</h2>
            <p>
                Todavía no tenés un rol asignado. Esperá a que un administrador
                complete tus datos y te habilite el acceso. Cuando lo haga, recargá
                la página y vas a ver tus opciones en el menú lateral.
            </p>
        </div>
    @endif

@endsection