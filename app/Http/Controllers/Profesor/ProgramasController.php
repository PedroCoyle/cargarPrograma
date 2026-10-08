<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgramasController extends Controller
{
    public function index()
{
    $profesor = auth()->user()->profesor;
    $anioActual = now()->year;

    $materias = $profesor->materias()->with(['carrera', 'anio'])->orderBy('nombre')->get();

    $programas = Programa::where('profesor_id', $profesor->id)
        ->where('anio_lectivo', $anioActual)
        ->orderByDesc('fecha_subida')
        ->get()
        ->groupBy('materia_id');

    return view('profesor.programas-index', [
        'materias' => $materias,
        'programas' => $programas,
    ]);
}

public function create(Materia $materia)
{
    $this->authorizeMateria($materia);

    $anioActual = now()->year;
    $profesor = auth()->user()->profesor;

    $cantidadActual = Programa::where('profesor_id', $profesor->id)
        ->where('materia_id', $materia->id)
        ->where('anio_lectivo', $anioActual)
        ->count();

    if ($cantidadActual >= 3) {
        return redirect()
            ->route('profesor.programas.index')
            ->with('error', 'Ya subiste los 3 archivos requeridos para esta materia en el año actual.');
    }

    return view('profesor.programas-create', [
        'materia' => $materia,
        'anioActual' => $anioActual,
    ]);
}

   public function store(Request $request, Materia $materia)
{
    $this->authorizeMateria($materia);

    $anioActual = now()->year;

    $profesor = auth()->user()->profesor;

    $cantidadActual = Programa::where('profesor_id', $profesor->id)
        ->where('materia_id', $materia->id)
        ->where('anio_lectivo', $anioActual)
        ->count();

    if ($cantidadActual >= 3) {
        return redirect()
            ->route('profesor.programas.index')
            ->with('error', 'Ya subiste los 3 archivos requeridos para esta materia en el año actual.');
    }

    $validated = $request->validate([
        'archivo' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        'anio_lectivo' => ['required', 'integer', "in:{$anioActual}"],
    ]);

    $archivo = $validated['archivo'];

    $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = $archivo->getClientOriginalExtension();

    $nombreSeguro = \Illuminate\Support\Str::slug($nombreOriginal);
    $sufijo = substr(uniqid(), -6);

    $nombreFinal = "{$nombreSeguro}-{$sufijo}.{$extension}";

    $path = $archivo->storeAs('programas', $nombreFinal, 'public');

    Programa::create([
        'profesor_id' => $profesor->id,
        'materia_id' => $materia->id,
        'anio_lectivo' => $validated['anio_lectivo'],
        'archivo' => $path,
        'fecha_subida' => now(),
        'ultimo' => true,
    ]);

    return redirect()
        ->route('profesor.programas.index')
        ->with('success', 'Programa subido correctamente.');
}

    private function authorizeMateria(Materia $materia): void
    {
        $profesor = auth()->user()->profesor;

        $tieneAsignada = $profesor->materias()->where('materias.id', $materia->id)->exists();

        if (! $tieneAsignada) {
            abort(403, 'No tenés esta materia asignada.');
        }
    }
}