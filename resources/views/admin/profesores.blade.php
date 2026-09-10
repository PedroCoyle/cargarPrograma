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
            <a href="{{ route('admin.profesores') }}" class="btn btn-outline">Ver todos</a>
        @else
            <a href="{{ route('admin.profesores', ['sin_rol' => 1]) }}" class="btn btn-primary">Ver sin rango</a>
        @endif

    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

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
                            @if(is_null($profesor->rol_id))
                                <span class="badge badge-sin-rol">Sin asignar</span>
                            @elseif($profesor->rol_id === \App\Models\Profesor::ROL_ADMIN)
                                <span class="badge badge-admin">Admin</span>
                            @elseif($profesor->rol_id === \App\Models\Profesor::ROL_PROFESOR)
                                <span class="badge badge-profesor">Profesor</span>
                            @else
                                <span class="badge badge-preceptor">Preceptor</span>
                            @endif
                        </td>
                        <td class="actions-cell">

                            <a href="{{ route('admin.profesores.edit', $profesor) }}" class="btn btn-outline btn-sm">
                                @if(is_null($profesor->rol_id))
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