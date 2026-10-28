<?php

namespace App\Http\Controllers\Jefatura;

use App\Models\Anio;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JefaturaAsignacionesController extends JefaturaController
{
    // ---------- Profesor: materias ----------

    public function materias(Request $request, Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_PROFESOR), 404);

        $ids = $this->carrerasIds();

        $carreraId = $request->query('carrera_id');
        $anioId = $request->query('anio_id');

        if ($carreraId && ! in_array((int) $carreraId, $ids, true)) {
            $carreraId = null;
        }

        $materiasFiltradas = collect();

        if ($carreraId && $anioId) {
            $materiasFiltradas = Materia::where('carrera_id', $carreraId)
                ->where('anio_id', $anioId)
                ->orderBy('nombre')
                ->get();
        }

        // Solo ve las materias de su área
        $asignadas = $profesor->materias()
            ->whereIn('materias.carrera_id', $ids)
            ->with(['carrera', 'anio'])
            ->orderBy('materias.nombre')
            ->get();

        return view('jefatura.asignar-materias', [
            'profesor' => $profesor,
            'carreras' => $this->carrerasDelJefe(),
            'anios' => Anio::orderBy('id')->get(),
            'carreraId' => $carreraId,
            'anioId' => $anioId,
            'materiasFiltradas' => $materiasFiltradas,
            'asignadas' => $asignadas,
            'asignadasIds' => $asignadas->pluck('id')->toArray(),
        ]);
    }

    public function updateMaterias(Request $request, Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_PROFESOR), 404);

        $validated = $request->validate([
            'carrera_id' => ['required', Rule::in($this->carrerasIds())],
            'anio_id' => ['required', 'exists:anios,id'],
            'materias' => ['array'],
            'materias.*' => ['integer', 'exists:materias,id'],
        ]);

        $materiaIdsEnFiltro = Materia::where('carrera_id', $validated['carrera_id'])
            ->where('anio_id', $validated['anio_id'])
            ->pluck('id')
            ->toArray();

        // Solo se tocan las materias de esta carrera y año
        $profesor->materias()->detach($materiaIdsEnFiltro);

        $seleccionadas = array_intersect($validated['materias'] ?? [], $materiaIdsEnFiltro);
        $profesor->materias()->attach($seleccionadas);

        return redirect()
            ->route('jefatura.perfiles.materias', [
                'profesor' => $profesor,
                'carrera_id' => $validated['carrera_id'],
                'anio_id' => $validated['anio_id'],
            ])
            ->with('success', 'Materias actualizadas correctamente.');
    }

    // ---------- EMTP: carreras (dentro de su área) ----------

    public function carreras(Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_EMTP), 404);

        return view('jefatura.asignar-carreras', [
            'profesor' => $profesor,
            'carreras' => $this->carrerasDelJefe(),
            'asignadasIds' => $profesor->carrerasEmtp()
                ->whereIn('carreras.id', $this->carrerasIds())
                ->pluck('carreras.id')
                ->toArray(),
        ]);
    }

    public function updateCarreras(Request $request, Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_EMTP), 404);

        $ids = $this->carrerasIds();

        $validated = $request->validate([
            'carreras' => ['array'],
            'carreras.*' => ['integer', Rule::in($ids)],
        ]);

        // Solo se tocan las carreras de su área; las demás quedan como estaban
        $profesor->carrerasEmtp()->detach($ids);
        $profesor->carrerasEmtp()->attach(array_values(array_unique($validated['carreras'] ?? [])));

        return redirect()
            ->route('jefatura.perfiles.carreras', $profesor)
            ->with('success', 'Carreras actualizadas correctamente.');
    }

    // ---------- Preceptor: cursos (dentro de su área, 4° a 7°) ----------

    public function cursos(Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_PRECEPTOR), 404);

        $asignados = $profesor->cursosPreceptor()
            ->whereIn('carrera_id', $this->carrerasIds())
            ->get()
            ->map(fn ($c) => $c->carrera_id . '-' . $c->anio_id)
            ->toArray();

        return view('jefatura.asignar-cursos', [
            'profesor' => $profesor,
            'carreras' => $this->carrerasDelJefe(),
            'anios' => Anio::whereBetween('id', [4, 7])->orderBy('id')->get(),
            'asignados' => $asignados,
        ]);
    }

    public function updateCursos(Request $request, Profesor $profesor)
    {
        $this->perfil($profesor);
        abort_unless($profesor->tieneRol(Profesor::ROL_PRECEPTOR), 404);

        $ids = $this->carrerasIds();

        $validated = $request->validate([
            'cursos' => ['array'],
            'cursos.*' => ['regex:/^\d+-\d+$/'],
        ]);

        $aniosValidos = Anio::whereBetween('id', [4, 7])->pluck('id')->all();

        $pares = collect($validated['cursos'] ?? [])
            ->map(function ($valor) {
                [$carreraId, $anioId] = explode('-', $valor);

                return ['carrera_id' => (int) $carreraId, 'anio_id' => (int) $anioId];
            })
            ->filter(fn ($p) => in_array($p['carrera_id'], $ids, true) && in_array($p['anio_id'], $aniosValidos, true))
            ->unique(fn ($p) => $p['carrera_id'] . '-' . $p['anio_id']);

        DB::transaction(function () use ($profesor, $ids, $pares) {
            // Solo se reemplazan los cursos de sus carreras
            $profesor->cursosPreceptor()->whereIn('carrera_id', $ids)->delete();

            foreach ($pares as $par) {
                $profesor->cursosPreceptor()->create($par);
            }
        });

        return redirect()
            ->route('jefatura.perfiles.cursos', $profesor)
            ->with('success', 'Cursos actualizados correctamente.');
    }
}

