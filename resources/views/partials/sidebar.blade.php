<aside class="sidebar">

    <div class="sidebar-role">
        Este usuario es
        @if(auth()->user()->rol_id === \App\Models\Profesor::ROL_ADMIN)
            administrador
        @elseif(auth()->user()->rol_id === \App\Models\Profesor::ROL_PRECEPTOR)
            preceptor
        @elseif(auth()->user()->rol_id === \App\Models\Profesor::ROL_PROFESOR)
            profesor
        @else
            sin rango asignado
        @endif
    </div>

    @if(auth()->user()->rol_id === \App\Models\Profesor::ROL_ADMIN)
    <nav class="sidebar-nav">
        <a href="{{ route('admin.profesores') }}">Ver profesores</a>
        <a href="{{ route('admin.profesores.materias.index') }}">Asignar Materias</a>
    </nav>
    @endif

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit" class="btn btn-outline logout-button">
            Cerrar sesión
        </button>
    </form>

</aside>