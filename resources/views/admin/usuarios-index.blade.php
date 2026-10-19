@extends('layouts.app')

@section('title', 'Usuarios y roles')

@section('content')

    <div class="section-header">
        <h1>Usuarios y roles</h1>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('general'))
        <div class="auth-errors">{{ $errors->first('general') }}</div>
    @endif

    <div class="filter-bar" style="margin-bottom: 20px;">

        <form method="GET" action="{{ route('admin.usuarios') }}" class="filter-form">

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

            <div class="form-group">
                <label for="estado">Perfil</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="sin_perfil" @selected($estado === 'sin_perfil')>Sin perfil asociado</option>
                    <option value="con_perfil" @selected($estado === 'con_perfil')>Con perfil asociado</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>

            @if($busqueda !== '' || $estado)
                <a href="{{ route('admin.usuarios') }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>

    </div>

    <div class="data-table-wrapper">

        <table class="data-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nombre de usuario</th>
                    <th>Perfil asociado</th>
                    <th>Rango</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    @php
                        $perfil = $usuario->profesor;
                        $conPerfil = $perfil && $perfil->creado_por_admin;
                        $rolesIds = $conPerfil ? $perfil->roles_ids : [];
                    @endphp
                    <tr>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->nombre }}</td>
                        <td>
                            @if($conPerfil)
                                {{ $perfil->nombre }}
                            @else
                                <span class="badge badge-sin-rol">Sin perfil</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array(\App\Models\Profesor::ROL_ADMIN, $rolesIds))
                                <span class="badge badge-admin">Admin</span>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_DIRECTIVO, $rolesIds))
                                <span class="badge badge-directivo">Directivo</span>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_EMTP, $rolesIds))
                                <span class="badge badge-emtp">EMTP</span>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_PRECEPTOR, $rolesIds))
                                <span class="badge badge-preceptor">Preceptor</span>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_PROFESOR, $rolesIds))
                                <span class="badge badge-profesor">Profesor</span>
                            @endif
                        </td>
                        <td class="actions-cell">

                            @if($usuario->id === auth()->id())

                                <span class="badge badge-admin">Tu cuenta</span>

                            @else

                                <a href="{{ route('admin.usuarios.associate', $usuario) }}" class="btn btn-outline btn-sm">
                                    {{ $conPerfil ? 'Cambiar perfil' : 'Asociar perfil' }}
                                </a>

                                @if($conPerfil)
                                    <form
                                        action="{{ route('admin.usuarios.unlink', $usuario) }}"
                                        method="POST"
                                        onsubmit="return confirm('¿Desvincular esta cuenta del perfil? La persona deja de ver sus roles y asignaciones, y el perfil queda esperando registro.');"
                                        style="display: inline;"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Desvincular</button>
                                    </form>
                                @endif

                                <form
                                    action="{{ route('admin.usuarios.destroy', $usuario) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Eliminar esta cuenta? La persona no podrá ingresar. Si tiene un perfil, se conserva esperando registro.');"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>

                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

@endsection