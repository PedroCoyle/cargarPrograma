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

    $materias = $profesor->materias()->with(['carrera', 'anio'])->orderBy('nombre')->get();

    $programas = Programa::where('profesor_id', $profesor->id)
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

        return view('profesor.programas-create', [
            'materia' => $materia,
        ]);
    }

    public function store(Request $request, Materia $materia)
{
    $this->authorizeMateria($materia);

    $validated = $request->validate([
        'archivo' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
    ]);

    $profesor = auth()->user()->profesor;

    $path = $validated['archivo']->store('programas', 'public');

    Programa::create([
        'profesor_id' => $profesor->id,
        'materia_id' => $materia->id,
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