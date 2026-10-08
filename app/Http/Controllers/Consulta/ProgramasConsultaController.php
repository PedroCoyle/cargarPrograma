<?php

namespace App\Http\Controllers\Consulta;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramasConsultaController extends Controller
{
    const REQUERIDOS_POR_MATERIA = 3;

    public function index()
    {
        $perfil = auth()->user()->profesor;
        $anioActual = now()->year;

        $stats = $this->carrerasVisibles($perfil)->map(function ($carrera) use ($perfil, $anioActual) {
            $pares = $this->pares($carrera, $perfil);

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

        return view('consulta.programas-carreras', [
            'stats' => $stats,
            'anioActual' => $anioActual,
        ]);
    }

    public function show(Request $request, Carrera $carrera)
    {
        $perfil = auth()->user()->profesor;

        // Si no tiene ningún año visible de esta carrera, no puede entrar
        abort_unless($this->aniosVisibles($perfil, $carrera->id) !== [], 403);

        $busqueda = $request->query('buscar');
        $anioActual = now()->year;

        $pares = $this->pares($carrera, $perfil);

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

        if ($busqueda) {
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

        return view('consulta.programas-carrera-show', [
            'carrera' => $carrera,
            'grupos' => $grupos,
            'busqueda' => $busqueda,
            'puedeHistorial' => $perfil->tieneRol(Profesor::ROL_DIRECTIVO),
        ]);
    }

    // Solo directivo (la ruta ya está protegida)
    public function historial(Profesor $profesor, Materia $materia)
    {
        $programasPorAnio = Programa::where('profesor_id', $profesor->id)
            ->where('materia_id', $materia->id)
            ->orderByDesc('anio_lectivo')
            ->orderBy('fecha_subida')
            ->get()
            ->groupBy('anio_lectivo');

        return view('consulta.programas-historial', [
            'profesor' => $profesor,
            'materia' => $materia,
            'programasPorAnio' => $programasPorAnio,
        ]);
    }

    // ---------- Alcance según los roles del usuario ----------

    private function carrerasVisibles(Profesor $perfil)
    {
        if ($perfil->tieneRol(Profesor::ROL_DIRECTIVO)) {
            return Carrera::orderBy('nombre')->get();
        }

        $ids = collect();

        if ($perfil->tieneRol(Profesor::ROL_EMTP)) {
            $ids = $ids->merge($perfil->carrerasEmtp()->pluck('carreras.id'));
        }

        if ($perfil->tieneRol(Profesor::ROL_PRECEPTOR)) {
            $ids = $ids->merge($perfil->cursosPreceptor()->pluck('carrera_id'));
        }

        return Carrera::whereIn('id', $ids->unique()->values()->all())
            ->orderBy('nombre')
            ->get();
    }

    /**
     * null  = todos los años de la carrera
     * []    = ninguno (no puede ver esa carrera)
     * [4,5] = solo esos años
     */
    private function aniosVisibles(Profesor $perfil, int $carreraId): ?array
    {
        if ($perfil->tieneRol(Profesor::ROL_DIRECTIVO)) {
            return null;
        }

        if ($perfil->tieneRol(Profesor::ROL_EMTP)
            && $perfil->carrerasEmtp()->where('carreras.id', $carreraId)->exists()) {
            return null;
        }

        if ($perfil->tieneRol(Profesor::ROL_PRECEPTOR)) {
            return $perfil->cursosPreceptor()
                ->where('carrera_id', $carreraId)
                ->pluck('anio_id')
                ->all();
        }

        return [];
    }

    private function pares(Carrera $carrera, Profesor $perfil)
    {
        $anios = $this->aniosVisibles($perfil, $carrera->id);

        $query = DB::table('profesor_materias')
            ->join('materias', 'materias.id', '=', 'profesor_materias.materia_id')
            ->where('materias.carrera_id', $carrera->id)
            ->select('profesor_materias.profesor_id', 'profesor_materias.materia_id');

        if ($anios !== null) {
            $query->whereIn('materias.anio_id', $anios);
        }

        return $query->get();
    }
}