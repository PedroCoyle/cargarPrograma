@extends('layouts.app')

@section('title', 'Profesores')

@section('content')

    <div class="section-header">

        <h1>
            @if($sinRol)
                Profesores sin rango asignado
            @else
                Todos los profesores
            @endif
        </h1>

        @if($sinRol)
            <a href="{{ route('admin.profesores', array_filter(['buscar' => $busqueda])) }}" class="btn btn-outline">Ver todos</a>
        @else
            <a href="{{ route('admin.profesores', array_filter(['sin_rol' => 1, 'buscar' => $busqueda])) }}" class="btn btn-primary">Ver sin rango</a>
        @endif

    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    <div class="filter-bar" style="margin-bottom: 20px;">

        <form method="GET" action="{{ route('admin.profesores') }}" class="filter-form">

            @if($sinRol)
                <input type="hidden" name="sin_rol" value="1">
            @endif

            <div class="form-group">
                <label for="buscar">Buscar por nombre o email</label>
                <input
                    type="text"
                    id="buscar"
                    name="buscar"
                    value="{{ $busqueda }}"
                    placeholder="Buscar..."
                >
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>

            @if($busqueda !== '')
                <a href="{{ route('admin.profesores', array_filter(['sin_rol' => $sinRol ? 1 : null])) }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>

    </div>

    <div class="data-table-wrapper">

        <table class="data-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nombre de usuario</th>
                    <th>Nombre asignado</th>
                    <th>Rango</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesores as $profesor)
                    <tr>
                        <td>{{ $profesor->user->email }}</td>
                        <td>{{ $profesor->user->nombre }}</td>
                        <td>{{ $profesor->nombre ?? '—' }}</td>
                        <td>
                            @php $rolesIds = $profesor->roles_ids; @endphp

                            @if(empty($rolesIds))
                                <span class="badge badge-sin-rol">Sin asignar</span>
                            @else
                                @if(in_array(\App\Models\Profesor::ROL_ADMIN, $rolesIds))
                                    <span class="badge badge-admin">Admin</span>
                                @endif
                                @if(in_array(\App\Models\Profesor::ROL_PRECEPTOR, $rolesIds))
                                    <span class="badge badge-preceptor">Preceptor</span>
                                @endif
                                @if(in_array(\App\Models\Profesor::ROL_PROFESOR, $rolesIds))
                                    <span class="badge badge-profesor">Profesor</span>
                                @endif
                                @if(in_array(\App\Models\Profesor::ROL_EMTP, $rolesIds))
                                    <span class="badge badge-emtp">EMTP</span>
                                @endif
                                @if(in_array(\App\Models\Profesor::ROL_DIRECTIVO, $rolesIds))
                                    <span class="badge badge-directivo">Directivo</span>
                                @endif
                            @endif
                        </td>
                        <td class="actions-cell">

                            <a href="{{ route('admin.profesores.edit', $profesor) }}" class="btn btn-outline btn-sm">
                                @if(empty($rolesIds))
                                    Asignar rol
                                @else
                                    Editar
                                @endif
                            </a>

                            <form
                                action="{{ route('admin.profesores.destroy', $profesor) }}"
                                method="POST"
                                onsubmit="return confirm('¿Seguro que querés eliminar este usuario? Esta acción no se puede deshacer.');"
                                style="display: inline;"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">No hay registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

@endsection