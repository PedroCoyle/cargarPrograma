<?php

namespace App\Http\Controllers\Jefatura;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JefaturaUsuariosController extends JefaturaController
{
    public function index(Request $request)
    {
        $jefe = $this->jefe();
        $busqueda = trim((string) $request->query('buscar'));
        $estado = $request->query('estado'); // sin_perfil | con_perfil | null

        // Cuentas sin perfil, o con un perfil que el jefe puede manejar
        $query = User::with('profesor')->where(function ($q) use ($jefe) {
            $q->whereDoesntHave('profesor', fn ($p) => $p->where('creado_por_admin', true))
              ->orWhereHas('profesor', fn ($p) => $p->gestionablePor($jefe));
        });

        if ($estado === 'sin_perfil') {
            $query->whereDoesntHave('profesor', fn ($p) => $p->where('creado_por_admin', true));
        } elseif ($estado === 'con_perfil') {
            $query->whereHas('profesor', fn ($p) => $p->gestionablePor($jefe));
        }

        if ($busqueda !== '') {
            $termino = '%' . mb_strtolower($busqueda) . '%';

            $query->where(function ($q) use ($termino) {
                $q->whereRaw('LOWER(users.nombre) LIKE ?', [$termino])
                  ->orWhereRaw('LOWER(users.email) LIKE ?', [$termino])
                  ->orWhereHas('profesor', function ($p) use ($termino) {
                      $p->whereRaw('LOWER(profesores.nombre) LIKE ?', [$termino]);
                  });
            });
        }

        return view('jefatura.usuarios-index', [
            'usuarios' => $query->orderByDesc('created_at')->get(),
            'busqueda' => $busqueda,
            'estado' => $estado,
        ]);
    }

    public function asociar(User $user)
    {
        abort_unless($this->usuarioVisible($user), 404);

        // Perfiles de su área que todavía no tienen cuenta
        $perfiles = Profesor::gestionablePor($this->jefe())
            ->where('creado_por_admin', true)
            ->whereNull('profesores.user_id')
            ->orderBy('nombre')
            ->get();

        return view('jefatura.usuarios-asociar', [
            'usuario' => $user->load('profesor'),
            'perfiles' => $perfiles,
        ]);
    }

    public function asociarStore(Request $request, User $user)
    {
        abort_unless($this->usuarioVisible($user), 404);

        $validated = $request->validate([
            'perfil_id' => ['required', 'exists:profesores,id'],
        ], [
            'perfil_id.required' => 'Elegí un perfil.',
        ]);

        $disponible = Profesor::gestionablePor($this->jefe())
            ->whereKey($validated['perfil_id'])
            ->where('creado_por_admin', true)
            ->whereNull('profesores.user_id')
            ->exists();

        if (! $disponible) {
            return back()->withErrors(['perfil_id' => 'Ese perfil no está disponible.']);
        }

        $perfil = Profesor::findOrFail($validated['perfil_id']);
        $actual = $user->profesor;

        if ($actual && ! $actual->creado_por_admin && ! $actual->estaVacio()) {
            return back()->withErrors([
                'perfil_id' => 'Esta cuenta tiene un perfil con datos y no se puede reemplazar.',
            ]);
        }

        DB::transaction(function () use ($user, $perfil, $actual) {
            if ($actual) {
                if ($actual->creado_por_admin) {
                    // Cambio de perfil: el anterior queda esperando registro
                    $actual->update(['user_id' => null]);
                } else {
                    // Perfil vacío de cuenta nueva: se descarta
                    $actual->delete();
                }
            }

            $perfil->update(['user_id' => $user->id]);
        });

        return redirect()
            ->route('jefatura.usuarios')
            ->with('success', "La cuenta {$user->email} quedó asociada al perfil de {$perfil->nombre}.");
    }

    public function desvincular(User $user)
    {
        abort_unless($this->usuarioVisible($user), 404);

        $perfil = $user->profesor;

        if (! $perfil || ! $perfil->creado_por_admin) {
            return back()->withErrors(['general' => 'Esa cuenta no tiene un perfil asociado.']);
        }

        DB::transaction(function () use ($user, $perfil) {
            $perfil->update(['user_id' => null]);

            // La cuenta vuelve a quedar como una cuenta nueva, sin roles
            Profesor::create(['user_id' => $user->id]);
        });

        return redirect()
            ->route('jefatura.usuarios')
            ->with('success', "Se desvinculó la cuenta {$user->email} del perfil de {$perfil->nombre}.");
    }

    // Una cuenta es visible si no tiene perfil o si su perfil es de su área y sin roles privilegiados
    private function usuarioVisible(User $user): bool
    {
        $perfil = $user->profesor;

        if (! $perfil || ! $perfil->creado_por_admin) {
            return true;
        }

        return $perfil->esGestionablePor($this->jefe());
    }
}