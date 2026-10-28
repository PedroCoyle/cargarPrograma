<?php

namespace App\Http\Controllers\Jefatura;

use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JefaturaProgramasController extends JefaturaController
{
    const REQUERIDOS_POR_MATERIA = 3;

    public function index()
    {
        $anioActual = now()->year;

        $stats = $this->carrerasDelJefe()->map(function ($carrera) use ($anioActual) {
            $pares = $this->pares($carrera);

            $cargados = 0;

            foreach ($pares as $par) {
                $cantidad = Programa::where('profesor_id', $par->profesor_id)
                    ->where('materia_id', $par->materia_id)
                    ->where('anio_lectivo', $anioActual)
                    ->count();

                if ($cantidad >= self::REQUERIDOS_POR_MATERIA) {
                    $cargados++;
                }
            }

            return [
                'carrera' => $carrera,
                'requerido' => $pares->count(),
                'cargados' => $cargados,
            ];
        });

        return view('jefatura.programas-carreras', [
            'stats' => $stats,
            'anioActual' => $anioActual,
        ]);
    }

    public function show(Request $request, Carrera $carrera)
    {
        $this->autorizarCarrera($carrera->id);

        $busqueda = trim((string) $request->query('buscar'));
        $anioActual = now()->year;

        $pares = $this->pares($carrera);

        $profesores = Profesor::with('user')
            ->whereIn('id', $pares->pluck('profesor_id')->unique()->values()->all())
            ->get()
            ->keyBy('id');

        $materias = Materia::with('anio')
            ->whereIn('id', $pares->pluck('materia_id')->unique()->values()->all())
            ->get()
            ->keyBy('id');

        $programas = Programa::where('anio_lectivo', $anioActual)
            ->whereIn('profesor_id', $profesores->keys()->all())
            ->whereIn('materia_id', $materias->keys()->all())
            ->orderBy('fecha_subida')
            ->get()
            ->groupBy(fn ($p) => $p->profesor_id . '-' . $p->materia_id);

        $grupos = $pares->map(function ($par) use ($profesores, $materias, $programas) {
            $items = $programas->get($par->profesor_id . '-' . $par->materia_id, collect())->values();

            return [
                'profesor' => $profesores[$par->profesor_id],
                'materia' => $materias[$par->materia_id],
                'programas' => $items,
                'completo' => $items->count() >= self::REQUERIDOS_POR_MATERIA,
            ];
        });

        if ($busqueda !== '') {
            $grupos = $grupos->filter(function ($g) use ($busqueda) {
                $nombre = $g['profesor']->nombre ?? '';
                $email = $g['profesor']->user->email ?? '';

                return str_contains(mb_strtolower($nombre), mb_strtolower($busqueda))
                    || str_contains(mb_strtolower($email), mb_strtolower($busqueda));
            });
        }

        $grupos = $grupos
            ->sortBy(fn ($g) => $g['profesor']->nombre ?? $g['profesor']->user->email)
            ->values();

        return view('jefatura.programas-carrera-show', [
            'carrera' => $carrera,
            'grupos' => $grupos,
            'busqueda' => $busqueda,
        ]);
    }

    public function historial(Profesor $profesor, Materia $materia)
    {
        $this->autorizarCarrera($materia->carrera_id);

        $programasPorAnio = Programa::where('profesor_id', $profesor->id)
            ->where('materia_id', $materia->id)
            ->orderByDesc('anio_lectivo')
            ->orderBy('fecha_subida')
            ->get()
            ->groupBy('anio_lectivo');

        return view('jefatura.programas-historial', [
            'profesor' => $profesor,
            'materia' => $materia,
            'programasPorAnio' => $programasPorAnio,
        ]);
    }

    public function destroy(Programa $programa)
    {
        $this->autorizarCarrera($programa->materia->carrera_id);

        Storage::disk('public')->delete($programa->archivo);

        $programa->delete();

        return back()->with('success', 'Programa eliminado correctamente.');
    }

    // ---------- Helpers ----------

    private function pares(Carrera $carrera)
    {
        return DB::table('profesor_materias')
            ->join('materias', 'materias.id', '=', 'profesor_materias.materia_id')
            ->where('materias.carrera_id', $carrera->id)
            ->select('profesor_materias.profesor_id', 'profesor_materias.materia_id')
            ->get();
    }

    private function autorizarCarrera(int $carreraId): void
    {
        abort_unless(in_array($carreraId, $this->carrerasIds(), true), 404);
    }
}