<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProgramasController extends Controller
{
    const REQUERIDOS_POR_MATERIA = 3;

    public function index()
{
    $carreras = Carrera::orderBy('nombre')->get();
    $anioActual = now()->year;

    $stats = $carreras->map(function ($carrera) use ($anioActual) {
        $pares = DB::table('profesor_materias')
            ->join('materias', 'materias.id', '=', 'profesor_materias.materia_id')
            ->where('materias.carrera_id', $carrera->id)
            ->select('profesor_materias.profesor_id', 'profesor_materias.materia_id')
            ->get();

        $requerido = $pares->count();

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
            'requerido' => $requerido,
            'cargados' => $cargados,
        ];
    });

    return view('admin.programas-carreras', [
        'stats' => $stats,
        'anioActual' => $anioActual,
    ]);
}

    public function show(Request $request, Carrera $carrera)
{
    $busqueda = $request->query('buscar');
    $anioActual = now()->year;

    $pares = DB::table('profesor_materias')
        ->join('materias', 'materias.id', '=', 'profesor_materias.materia_id')
        ->where('materias.carrera_id', $carrera->id)
        ->select('profesor_materias.profesor_id', 'profesor_materias.materia_id')
        ->get();

    $grupos = $pares->map(function ($par) use ($anioActual) {
        $profesor = Profesor::with('user')->find($par->profesor_id);
        $materia = Materia::with('anio')->find($par->materia_id);

        $programas = Programa::where('profesor_id', $par->profesor_id)
            ->where('materia_id', $par->materia_id)
            ->where('anio_lectivo', $anioActual)
            ->orderBy('fecha_subida')
            ->get();

        return [
            'profesor' => $profesor,
            'materia' => $materia,
            'programas' => $programas,
            'completo' => $programas->count() >= self::REQUERIDOS_POR_MATERIA,
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

    return view('admin.programas-carrera-show', [
        'carrera' => $carrera,
        'grupos' => $grupos,
        'busqueda' => $busqueda,
    ]);
}

    public function destroy(Programa $programa)
    {
        Storage::disk('public')->delete($programa->archivo);

        $programa->delete();

        return redirect()
            ->back()
            ->with('success', 'Programa eliminado correctamente.');
    }


    public function historial(Profesor $profesor, Materia $materia)
{
    $programas = Programa::where('profesor_id', $profesor->id)
        ->where('materia_id', $materia->id)
        ->orderByDesc('anio_lectivo')
        ->orderBy('fecha_subida')
        ->get()
        ->groupBy('anio_lectivo');

    return view('admin.programas-historial', [
        'profesor' => $profesor,
        'materia' => $materia,
        'programasPorAnio' => $programas,
    ]);
}
}

