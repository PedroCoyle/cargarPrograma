<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarrerasController extends Controller
{
    public function index()
    {
        $carreras = Carrera::withCount('materias')->orderBy('nombre')->get();

        return view('admin.carreras-index', [
            'carreras' => $carreras,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:carreras,nombre'],
        ]);

        Carrera::create($validated);

        return redirect()
            ->route('admin.carreras.index')
            ->with('success', 'Carrera creada correctamente.');
    }

    public function destroy(Carrera $carrera)
    {
        // Borramos los archivos físicos de los programas asociados antes de que
        // la cascada elimine las filas de materias / profesor_materias / programas
        $programas = Programa::whereHas('materia', function ($q) use ($carrera) {
            $q->where('carrera_id', $carrera->id);
        })->get();

        foreach ($programas as $programa) {
            Storage::disk('public')->delete($programa->archivo);
        }

        $carrera->delete();

        return redirect()
            ->route('admin.carreras.index')
            ->with('success', 'Carrera eliminada correctamente.');
    }

    public function show(Carrera $carrera)
    {
        $anios = Anio::orderBy('id')->get();

        $materias = Materia::where('carrera_id', $carrera->id)
            ->with('anio')
            ->orderBy('anio_id')
            ->orderBy('nombre')
            ->get();

        return view('admin.carreras-show', [
            'carrera' => $carrera,
            'anios' => $anios,
            'materias' => $materias,
        ]);
    }

    public function storeMateria(Request $request, Carrera $carrera)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'anio_id' => ['required', 'exists:anios,id'],
        ]);

        Materia::create([
            'carrera_id' => $carrera->id,
            'anio_id' => $validated['anio_id'],
            'nombre' => $validated['nombre'],
        ]);

        return redirect()
            ->route('admin.carreras.show', $carrera)
            ->with('success', 'Materia agregada correctamente.');
    }

    public function destroyMateria(Materia $materia)
    {
        $carreraId = $materia->carrera_id;

        $programas = Programa::where('materia_id', $materia->id)->get();

        foreach ($programas as $programa) {
            Storage::disk('public')->delete($programa->archivo);
        }

        $materia->delete();

        return redirect()
            ->route('admin.carreras.show', $carreraId)
            ->with('success', 'Materia eliminada correctamente.');
    }
}