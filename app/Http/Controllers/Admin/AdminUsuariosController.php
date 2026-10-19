<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUsuariosController extends Controller
{
    // ---------- Listado de cuentas ----------

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar'));
        $estado = $request->query('estado'); // sin_perfil | con_perfil | null

        $query = User::with('profesor');

        if ($estado === 'sin_perfil') {
            $query->whereDoesntHave('profesor', fn ($q) => $q->where('creado_por_admin', true));
        } elseif ($estado === 'con_perfil') {
            $query->whereHas('profesor', fn ($q) => $q->where('creado_por_admin', true));
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

        return view('admin.usuarios-index', [
            'usuarios' => $query->orderByDesc('created_at')->get(),
            'busqueda' => $busqueda,
            'estado' => $estado,
        ]);
    }

    // ---------- Asociar / cambiar perfil ----------

    public function asociar(User $user)
    {
        // Perfiles creados por el admin que todavía no tienen cuenta
        $perfiles = Profesor::where('creado_por_admin', true)
            ->whereNull('user_id')
            ->orderBy('nombre')
            ->get();

        return view('admin.usuarios-asociar', [
            'usuario' => $user->load('profesor'),
            'perfiles' => $perfiles,
        ]);
    }

    public function asociarStore(Request $request, User $user)
    {
        $validated = $request->validate([
            'perfil_id' => ['required', 'exists:profesores,id'],
        ], [
            'perfil_id.required' => 'Elegí un perfil.',
        ]);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['perfil_id' => 'No podés cambiar el perfil de tu propia cuenta.']);
        }

        $perfil = Profesor::findOrFail($validated['perfil_id']);

        if (! $perfil->creado_por_admin || $perfil->tieneCuenta()) {
            return back()->withErrors(['perfil_id' => 'Ese perfil no está disponible.']);
        }

        $actual = $user->profesor;

        // Solo se reemplaza el perfil vacío de una cuenta nueva o el perfil armado por el admin
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
            ->route('admin.usuarios')
            ->with('success', "La cuenta {$user->email} quedó asociada al perfil de {$perfil->nombre}.");
    }

    // ---------- Desvincular ----------

    public function desvincular(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['general' => 'No podés desvincular tu propia cuenta.']);
        }

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
            ->route('admin.usuarios')
            ->with('success', "Se desvinculó la cuenta {$user->email} del perfil de {$perfil->nombre}.");
    }

    // ---------- Eliminar cuenta ----------

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['general' => 'No podés eliminar tu propia cuenta.']);
        }

        $perfil = $user->profesor;

        DB::transaction(function () use ($user, $perfil) {
            if ($perfil) {
                if ($perfil->creado_por_admin || ! $perfil->estaVacio()) {
                    // El perfil se conserva, con sus asignaciones y programas
                    $perfil->update([
                        'user_id' => null,
                        'nombre' => $perfil->nombre ?? $user->nombre,
                        'creado_por_admin' => true,
                    ]);
                } else {
                    $perfil->delete();
                }
            }

            $user->delete();
        });

        return redirect()
            ->route('admin.usuarios')
            ->with('success', 'Cuenta eliminada. Si tenía un perfil, se conservó esperando registro.');
    }
}