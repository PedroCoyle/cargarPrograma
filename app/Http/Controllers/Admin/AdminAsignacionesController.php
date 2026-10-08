<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAsignacionesController extends Controller
{
    // ---------- EMTP: carreras completas ----------

    public function carreras(Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_EMTP), 404);

        return view('admin.profesores-carreras', [
            'profesor' => $profesor,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'asignadasIds' => $profesor->carrerasEmtp()->pluck('carreras.id')->toArray(),
        ]);
    }

    public function updateCarreras(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_EMTP), 404);

        $validated = $request->validate([
            'carreras' => ['array'],
            'carreras.*' => ['integer', 'exists:carreras,id'],
        ]);

        $profesor->carrerasEmtp()->sync($validated['carreras'] ?? []);

        return redirect()
            ->route('admin.profesores.carreras', $profesor)
            ->with('success', 'Carreras actualizadas correctamente.');
    }

    // ---------- Preceptor: carrera + año (4° a 7°) ----------

    public function cursos(Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_PRECEPTOR), 404);

        $asignados = $profesor->cursosPreceptor()
            ->get()
            ->map(fn ($c) => $c->carrera_id . '-' . $c->anio_id)
            ->toArray();

        return view('admin.profesores-cursos', [
            'profesor' => $profesor,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'anios' => Anio::whereBetween('id', [4, 7])->orderBy('id')->get(),
            'asignados' => $asignados,
        ]);
    }

    public function updateCursos(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_PRECEPTOR), 404);

        $validated = $request->validate([
            'cursos' => ['array'],
            'cursos.*' => ['regex:/^\d+-\d+$/'],
        ]);

        $carrerasValidas = Carrera::pluck('id')->all();
        $aniosValidos = Anio::whereBetween('id', [4, 7])->pluck('id')->all();

        $pares = collect($validated['cursos'] ?? [])
            ->map(function ($valor) {
                [$carreraId, $anioId] = explode('-', $valor);

                return ['carrera_id' => (int) $carreraId, 'anio_id' => (int) $anioId];
            })
            ->filter(fn ($p) => in_array($p['carrera_id'], $carrerasValidas)
                && in_array($p['anio_id'], $aniosValidos))
            ->unique(fn ($p) => $p['carrera_id'] . '-' . $p['anio_id']);

        DB::transaction(function () use ($profesor, $pares) {
            $profesor->cursosPreceptor()->delete();

            foreach ($pares as $par) {
                $profesor->cursosPreceptor()->create($par);
            }
        });

        return redirect()
            ->route('admin.profesores.cursos', $profesor)
            ->with('success', 'Cursos actualizados correctamente.');
    }
}