<aside class="sidebar">

    <ul>
        <li><a href="{{ route('dashboard') }}">Inicio</a></li>

        @if(auth()->user()->rol_id === 1) {{-- Admin --}}
            <li><a href="{{ route('admin.usuarios') }}">Gestionar usuarios</a></li>
            <li><a href="{{ route('admin.profesores') }}">Gestionar profesores</a></li>
        @endif

        @if(auth()->user()->rol_id === 2) {{-- Profesor --}}
            <li><a href="{{ route('profesor.materias') }}">Mis materias</a></li>
            <li><a href="{{ route('profesor.notas') }}">Cargar notas</a></li>
        @endif


        <aside class="sidebar">

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit" class="logout-button">
            Cerrar sesión
        </button>
    </form>

</aside>

    </ul>

</aside>