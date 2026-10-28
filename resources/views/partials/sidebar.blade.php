<aside class="sidebar">

    <div class="sidebar-logo">
        <img
            src="{{ asset('images/logo.png') }}"
            alt="Logo institucional"
            class="sidebar-logo-img"
        >
    </div>

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_ADMIN))
        <details class="sidebar-section" data-section="admin" open>
            <summary>Administración</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('admin.perfiles.index') }}">Perfiles</a>
                <a href="{{ route('admin.usuarios') }}">Usuarios y roles</a>
                <a href="{{ route('admin.carreras.index') }}">Carreras y materias</a>
                <a href="{{ route('admin.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_DIRECTIVO))
        <details class="sidebar-section" data-section="directivo" open>
            <summary>Dirección</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('consulta.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_JEFE))
        <details class="sidebar-section" data-section="jefe" open>
            <summary>Jefatura</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('jefatura.perfiles.index') }}">Perfiles</a>
                <a href="{{ route('jefatura.usuarios') }}">Usuarios y roles</a>
                <a href="{{ route('jefatura.carreras.index') }}">Carreras y materias</a>
                <a href="{{ route('jefatura.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_EMTP))
        <details class="sidebar-section" data-section="emtp" open>
            <summary>EMTP</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('consulta.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_PRECEPTOR))
        <details class="sidebar-section" data-section="preceptor" open>
            <summary>Preceptoría</summary>
            <nav class="sidebar-nav">
                <a href="{{ route('consulta.programas.index') }}">Programas cargados</a>
            </nav>
        </details>
    @endif

    @if(auth()->user()->tieneRol(\App\Models\Profesor::ROL_PROFESOR))
        <details class="sidebar-section" data-section="profesor" open>
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

    <script>
        (function () {
            document.querySelectorAll('.sidebar-section[data-section]').forEach(function (section) {
                var key = 'sidebar-section:' + section.dataset.section;

                try {
                    if (localStorage.getItem(key) === 'closed') {
                        section.removeAttribute('open');
                    }
                } catch (e) {}

                section.addEventListener('toggle', function () {
                    try {
                        localStorage.setItem(key, section.open ? 'open' : 'closed');
                    } catch (e) {}
                });
            });
        })();
    </script>

</aside>