<aside class="sidebar">

    <div class="sidebar-logo">
        <img
            src="{{ asset('images/logo.png') }}"
            alt="Logo institucional"
            class="sidebar-logo-img"
        >
    </div>

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_ADMIN))
        <details class="sidebar-section" open>
            <summary>Administración</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('admin.profesores') }}">Usuarios y roles</a>
                <a href="{{ route('admin.carreras.index') }}">Carreras y materias</a>
                <a href="{{ route('admin.profesores.materias.index') }}">Asignar materias</a>
                <a href="{{ route('admin.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_PRECEPTOR))
        <details class="sidebar-section">
            <summary>Preceptoría</summary>
            <nav class="sidebar-nav">
                <span class="sidebar-nav-empty">Próximamente</span>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_PROFESOR))
        <details class="sidebar-section">
            <summary>Mis materias</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('profesor.programas.index') }}">Cargar programas</a>
            </nav>
        </details>
    @endif

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit" class="btn btn-outline logout-button">
            Cerrar sesión
        </button>
    </form>

</aside>