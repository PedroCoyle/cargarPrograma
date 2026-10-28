<?php

namespace App\Http\Controllers\Jefatura;

use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Materia;
use Illuminate\Http\Request;

class JefaturaCarrerasController extends JefaturaController
{
    public function index()
    {
        $carreras = Carrera::whereIn('id', $this->carrerasIds())
            ->withCount('materias')
            ->orderBy('nombre')
            ->get();

        return view('jefatura.carreras-index', [
            'carreras' => $carreras,
        ]);
    }

    public function show(Request $request, Carrera $carrera)
    {
        $this->autorizarCarrera($carrera->id);

        $busqueda = trim((string) $request->query('buscar'));
        $anioFiltro = $request->integer('anio_id');

        $query = Materia::where('carrera_id', $carrera->id)
            ->with('anio')
            ->orderBy('anio_id')
            ->orderBy('nombre');

        if ($busqueda !== '') {
            $query->whereRaw('LOWER(materias.nombre) LIKE ?', ['%' . mb_strtolower($busqueda) . '%']);
        }

        if ($anioFiltro) {
            $query->where('anio_id', $anioFiltro);
        }

        return view('jefatura.carreras-show', [
            'carrera' => $carrera,
            'anios' => Anio::orderBy('id')->get(),
            'materias' => $query->get(),
            'busqueda' => $busqueda,
            'anioFiltro' => $anioFiltro,
            'ultimoAnioId' => session('jefatura_ultimo_anio_id'),
        ]);
    }

    public function storeMateria(Request $request, Carrera $carrera)
    {
        $this->autorizarCarrera($carrera->id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'anio_id' => ['required', 'exists:anios,id'],
        ]);

        Materia::create([
            'carrera_id' => $carrera->id,
            'anio_id' => $validated['anio_id'],
            'nombre' => trim($validated['nombre']),
        ]);

        // Recordar el último año elegido para la próxima carga
        session(['jefatura_ultimo_anio_id' => $validated['anio_id']]);

        return redirect()
            ->route('jefatura.carreras.show', $carrera)
            ->with('success', 'Materia agregada correctamente.');
    }

    public function editMateria(Materia $materia)
    {
        $this->autorizarCarrera($materia->carrera_id);

        return view('jefatura.materias-edit', [
            'materia' => $materia->load('carrera'),
            'anios' => Anio::orderBy('id')->get(),
        ]);
    }

    public function updateMateria(Request $request, Materia $materia)
    {
        $this->autorizarCarrera($materia->carrera_id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'anio_id' => ['required', 'exists:anios,id'],
        ]);

        $materia->update([
            'nombre' => trim($validated['nombre']),
            'anio_id' => $validated['anio_id'],
        ]);

        return redirect()
            ->route('jefatura.carreras.show', $materia->carrera_id)
            ->with('success', 'Materia actualizada correctamente.');
    }

    private function autorizarCarrera(int $carreraId): void
    {
        abort_unless(in_array($carreraId, $this->carrerasIds(), true), 404);
    }
}
