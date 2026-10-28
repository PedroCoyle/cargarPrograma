<?php

namespace App\Http\Controllers\Jefatura;

use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JefaturaPerfilesController extends JefaturaController
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar'));
        $estado = $request->query('estado'); // sin_cuenta | con_cuenta | null

        $query = Profesor::with('user')->gestionablePor($this->jefe());

        if ($estado === 'sin_cuenta') {
            $query->whereNull('profesores.user_id');
        } elseif ($estado === 'con_cuenta') {
            $query->whereNotNull('profesores.user_id');
        }

        if ($busqueda !== '') {
            $termino = '%' . mb_strtolower($busqueda) . '%';

            $query->where(function ($q) use ($termino) {
                $q->whereRaw('LOWER(profesores.nombre) LIKE ?', [$termino])
                  ->orWhereHas('user', function ($u) use ($termino) {
                      $u->whereRaw('LOWER(users.email) LIKE ?', [$termino]);
                  });
            });
        }

        return view('jefatura.perfiles-index', [
            'perfiles' => $query->orderBy('nombre')->get(),
            'busqueda' => $busqueda,
            'estado' => $estado,
            'sinCarreras' => empty($this->carrerasIds()),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in(Profesor::rolesQueAsignaElJefe())],
        ], $this->mensajes());

        $jefe = $this->jefe();

        $perfil = DB::transaction(function () use ($validated, $jefe) {
            $perfil = Profesor::create([
                'user_id' => null,
                'nombre' => trim($validated['nombre']),
                'creado_por_admin' => true,
                'creado_por_perfil_id' => $jefe->id,
            ]);

            $perfil->asignarRoles($validated['roles']);

            return $perfil;
        });

        return redirect()
            ->route('jefatura.perfiles.index')
            ->with('success', "Perfil de {$perfil->nombre} creado. Ya podés asignarle materias, carreras o cursos.");
    }

    public function edit(Profesor $profesor)
    {
        return view('jefatura.perfiles-edit', [
            'perfil' => $this->perfil($profesor),
        ]);
    }

    public function update(Request $request, Profesor $profesor)
    {
        $this->perfil($profesor);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in(Profesor::rolesQueAsignaElJefe())],
        ], $this->mensajes());

        DB::transaction(function () use ($profesor, $validated) {
            $profesor->update(['nombre' => trim($validated['nombre'])]);
            $profesor->asignarRoles($validated['roles']);
        });

        return redirect()
            ->route('jefatura.perfiles.index')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'roles.required' => 'Elegí al menos un rol.',
            'roles.min' => 'Elegí al menos un rol.',
        ];
    }
}