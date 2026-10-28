<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminPerfilesController extends Controller
{
    const PALABRA_CONFIRMACION = 'ELIMINAR';

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar'));
        $estado = $request->query('estado'); // sin_cuenta | con_cuenta | null

        $query = Profesor::with('user')->where('creado_por_admin', true);

        if ($estado === 'sin_cuenta') {
            $query->whereNull('user_id');
        } elseif ($estado === 'con_cuenta') {
            $query->whereNotNull('user_id');
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

        return view('admin.perfiles-index', [
            'perfiles' => $query->orderBy('nombre')->get(),
            'busqueda' => $busqueda,
            'estado' => $estado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($this->rolesValidos())],
        ], $this->mensajes());

        $perfil = DB::transaction(function () use ($validated) {
            $perfil = Profesor::create([
                'user_id' => null,
                'nombre' => trim($validated['nombre']),
                'creado_por_admin' => true,
            ]);

            $perfil->asignarRoles($validated['roles']);

            return $perfil;
        });

        return redirect()
            ->route('admin.perfiles.index')
            ->with('success', "Perfil de {$perfil->nombre} creado. Ya podés asignarle materias, carreras o cursos.");
    }

    public function edit(Profesor $profesor)
    {
        abort_unless($profesor->creado_por_admin, 404);

        return view('admin.perfiles-edit', [
            'perfil' => $profesor,
        ]);
    }

    public function update(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->creado_por_admin, 404);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in($this->rolesValidos())],
        ], $this->mensajes());

        // Para no quedarse sin acceso de administrador
        $roles = array_map('intval', $validated['roles']);

        if ($profesor->user_id === auth()->id() && ! in_array(Profesor::ROL_ADMIN, $roles, true)) {
            return back()
                ->withInput()
                ->withErrors(['roles' => 'No podés quitarte a vos mismo el rol de administrador.']);
        }

        DB::transaction(function () use ($profesor, $validated) {
            $profesor->update(['nombre' => trim($validated['nombre'])]);
            $profesor->asignarRoles($validated['roles']);
        });

        return redirect()
            ->route('admin.perfiles.index')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    // ---------- Eliminar ----------

    public function confirmarEliminacion(Profesor $profesor)
    {
        abort_unless($profesor->creado_por_admin, 404);

        $resumen = $this->resumenPerfil($profesor);

        return view('admin.perfiles-eliminar', [
            'perfil' => $profesor,
            'resumen' => $resumen,
            'requiereConfirmacion' => $this->hayDatos($resumen),
        ]);
    }

    public function destroy(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->creado_por_admin, 404);

        if ($profesor->user_id === auth()->id()) {
            return redirect()
                ->route('admin.perfiles.index')
                ->withErrors(['general' => 'No podés eliminar tu propio perfil.']);
        }

        $resumen = $this->resumenPerfil($profesor);

        if ($this->hayDatos($resumen) && ! $this->confirmacionValida($request)) {
            return redirect()
                ->route('admin.perfiles.delete', $profesor)
                ->withErrors(['confirmacion' => 'Tenés que escribir ELIMINAR para confirmar.']);
        }

        $userId = $profesor->user_id;
        $eliminarCuenta = $userId && $request->input('modo') === 'perfil_y_cuenta';
        $archivos = $profesor->programas()->pluck('archivo')->all();

        DB::transaction(function () use ($profesor, $userId, $eliminarCuenta) {
            $profesor->delete();

            if ($userId) {
                if ($eliminarCuenta) {
                    User::whereKey($userId)->delete();
                } else {
                    // La cuenta se conserva, sin roles ni datos
                    Profesor::create(['user_id' => $userId]);
                }
            }
        });

        Storage::disk('public')->delete($archivos);

        return redirect()
            ->route('admin.perfiles.index')
            ->with('success', 'Perfil eliminado correctamente.');
    }

    // ---------- Helpers ----------

    private function rolesValidos(): array
{
    return [
        Profesor::ROL_PROFESOR,
        Profesor::ROL_PRECEPTOR,
        Profesor::ROL_ADMIN,
        Profesor::ROL_EMTP,
        Profesor::ROL_DIRECTIVO,
        Profesor::ROL_JEFE,
    ];
}

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'roles.required' => 'Elegí al menos un rol.',
            'roles.min' => 'Elegí al menos un rol.',
        ];
    }

    private function resumenPerfil(Profesor $profesor): array
    {
        return [
            ['label' => 'roles asignados', 'count' => $profesor->rolesPivot()->count()],
            ['label' => 'materias asignadas', 'count' => $profesor->materias()->count()],
            ['label' => 'carreras asignadas (EMTP)', 'count' => $profesor->carrerasEmtp()->count()],
            ['label' => 'cursos asignados (preceptor)', 'count' => $profesor->cursosPreceptor()->count()],
            ['label' => 'programas cargados (también se borran sus archivos)', 'count' => $profesor->programas()->count()],
        ];
    }

    private function hayDatos(array $resumen): bool
    {
        return collect($resumen)->sum('count') > 0;
    }

    private function confirmacionValida(Request $request): bool
    {
        return mb_strtolower(trim((string) $request->input('confirmacion'))) === mb_strtolower(self::PALABRA_CONFIRMACION);
    }
}