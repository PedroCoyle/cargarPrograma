@extends('layouts.app')

@section('title', 'Perfiles')

@section('content')

    <div class="section-header">
        <h1>Perfiles</h1>
    </div>

    @if(session('success'))
        <div class="auth-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('general'))
        <div class="auth-errors">{{ $errors->first('general') }}</div>
    @endif

    {{-- Crear perfil --}}
    <div class="data-table-wrapper" style="padding: 24px; margin-bottom: 24px;">

        <h2 class="card-title">Nuevo perfil</h2>

        <form action="{{ route('admin.perfiles.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
            </div>

            <div class="roles-inline">
                @include('admin.partials.roles-checkboxes', ['seleccionados' => old('roles', [])])
            </div>

            @error('nombre')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            @error('roles')
                <div class="auth-errors">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary">Crear perfil</button>

        </form>

    </div>

    {{-- Filtros --}}
    <div class="filter-bar" style="margin-bottom: 20px;">

        <form method="GET" action="{{ route('admin.perfiles.index') }}" class="filter-form">

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
                <label for="estado">Cuenta</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="sin_cuenta" @selected($estado === 'sin_cuenta')>Esperando registro</option>
                    <option value="con_cuenta" @selected($estado === 'con_cuenta')>Con cuenta asociada</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>

            @if($busqueda !== '' || $estado)
                <a href="{{ route('admin.perfiles.index') }}" class="btn btn-outline">Limpiar</a>
            @endif

        </form>

    </div>

    {{-- Listado --}}
    <div class="data-table-wrapper">

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Rango</th>
                    <th>Cuenta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perfiles as $perfil)
                    @php $rolesIds = $perfil->roles_ids; @endphp
                    <tr>
                        <td>{{ $perfil->nombre }}</td>
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
                        <td>
                            @if($perfil->tieneCuenta())
                                {{ $perfil->user->email }}
                            @else
                                <span class="badge badge-pendiente">Esperando registro</span>
                            @endif
                        </td>
                        <td class="actions-cell">

                            @if(in_array(\App\Models\Profesor::ROL_PROFESOR, $rolesIds))
                                <a href="{{ route('admin.profesores.materias', $perfil) }}" class="btn btn-outline btn-sm">Materias</a>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_PRECEPTOR, $rolesIds))
                                <a href="{{ route('admin.profesores.cursos', $perfil) }}" class="btn btn-outline btn-sm">Cursos</a>
                            @endif
                            @if(in_array(\App\Models\Profesor::ROL_EMTP, $rolesIds))
                                <a href="{{ route('admin.profesores.carreras', $perfil) }}" class="btn btn-outline btn-sm">Carreras</a>
                            @endif

                            <a href="{{ route('admin.perfiles.edit', $perfil) }}" class="btn btn-outline btn-sm">Editar</a>

                            <a href="{{ route('admin.perfiles.delete', $perfil) }}" class="btn btn-danger btn-sm">Eliminar</a>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">Todavía no hay perfiles creados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

@endsection