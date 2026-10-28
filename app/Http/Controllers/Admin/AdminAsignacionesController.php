<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAsignacionesController extends Controller
{
    // ---------- Profesor: materias ----------

    public function materias(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_PROFESOR), 404);

        $carreras = Carrera::orderBy('nombre')->get();
        $anios = Anio::orderBy('id')->get();

        $carreraId = $request->query('carrera_id');
        $anioId = $request->query('anio_id');

        $materiasFiltradas = collect();

        if ($carreraId && $anioId) {
            $materiasFiltradas = Materia::where('carrera_id', $carreraId)
                ->where('anio_id', $anioId)
                ->orderBy('nombre')
                ->get();
        }

        $asignadas = $profesor->materias()->with(['carrera', 'anio'])->orderBy('nombre')->get();
        $asignadasIds = $asignadas->pluck('id')->toArray();

        return view('admin.profesores-materias', [
            'profesor' => $profesor,
            'carreras' => $carreras,
            'anios' => $anios,
            'carreraId' => $carreraId,
            'anioId' => $anioId,
            'materiasFiltradas' => $materiasFiltradas,
            'asignadas' => $asignadas,
            'asignadasIds' => $asignadasIds,
        ]);
    }

    public function updateMaterias(Request $request, Profesor $profesor)
    {
        abort_unless($profesor->tieneRol(Profesor::ROL_PROFESOR), 404);

        $validated = $request->validate([
            'carrera_id' => ['required', 'exists:carreras,id'],
            'anio_id' => ['required', 'exists:anios,id'],
            'materias' => ['array'],
            'materias.*' => ['integer', 'exists:materias,id'],
        ]);

        $materiaIdsEnFiltro = Materia::where('carrera_id', $validated['carrera_id'])
            ->where('anio_id', $validated['anio_id'])
            ->pluck('id')
            ->toArray();

        // Solo se tocan las materias de esta carrera y año, sin afectar otras asignaciones
        $profesor->materias()->detach($materiaIdsEnFiltro);

        $seleccionadas = array_intersect($validated['materias'] ?? [], $materiaIdsEnFiltro);
        $profesor->materias()->attach($seleccionadas);

        return redirect()
            ->route('admin.profesores.materias', [
                'profesor' => $profesor,
                'carrera_id' => $validated['carrera_id'],
                'anio_id' => $validated['anio_id'],
            ])
            ->with('success', 'Materias actualizadas correctamente.');
    }

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

    // ---------- Jefe de área / departamento: carreras de su área ----------

public function areas(Profesor $profesor)
{
    abort_unless($profesor->tieneRol(Profesor::ROL_JEFE), 404);

    return view('admin.profesores-areas', [
        'profesor' => $profesor,
        'carreras' => Carrera::orderBy('nombre')->get(),
        'asignadasIds' => $profesor->carrerasJefe()->pluck('carreras.id')->toArray(),
    ]);
}

public function updateAreas(Request $request, Profesor $profesor)
{
    abort_unless($profesor->tieneRol(Profesor::ROL_JEFE), 404);

    $validated = $request->validate([
        'carreras' => ['array'],
        'carreras.*' => ['integer', 'exists:carreras,id'],
    ]);

    $profesor->carrerasJefe()->sync($validated['carreras'] ?? []);

    return redirect()
        ->route('admin.profesores.areas', $profesor)
        ->with('success', 'Área actualizada correctamente.');
}
}