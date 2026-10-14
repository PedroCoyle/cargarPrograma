<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CarrerasController extends Controller
{
    const PALABRA_CONFIRMACION = 'ELIMINAR';

    // ---------- Carreras ----------

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


    public function edit(Carrera $carrera)
{
    return view('admin.carreras-edit', [
        'carrera' => $carrera,
    ]);
}

public function update(Request $request, Carrera $carrera)
{
    $validated = $request->validate([
        'nombre' => [
            'required',
            'string',
            'max:255',
            Rule::unique('carreras', 'nombre')->ignore($carrera->id),
        ],
    ]);

    $carrera->update([
        'nombre' => trim($validated['nombre']),
    ]);

    return redirect()
        ->route('admin.carreras.index')
        ->with('success', 'Carrera actualizada correctamente.');
}

    public function show(Request $request, Carrera $carrera)
    {
        $busqueda = trim((string) $request->query('buscar'));
        $anioFiltro = $request->integer('anio_id');

        $query = Materia::where('carrera_id', $carrera->id)
            ->with('anio')
            ->withCount(['profesores', 'programas'])
            ->orderBy('anio_id')
            ->orderBy('nombre');

        if ($busqueda !== '') {
            $query->whereRaw('LOWER(materias.nombre) LIKE ?', ['%' . mb_strtolower($busqueda) . '%']);
        }

        if ($anioFiltro) {
            $query->where('anio_id', $anioFiltro);
        }

        return view('admin.carreras-show', [
            'carrera' => $carrera,
            'anios' => Anio::orderBy('id')->get(),
            'materias' => $query->get(),
            'busqueda' => $busqueda,
            'anioFiltro' => $anioFiltro,
            'ultimoAnioId' => session('ultimo_anio_id'),
        ]);
    }

    public function confirmarEliminacion(Carrera $carrera)
    {
        $resumen = $this->resumenCarrera($carrera);

        return view('admin.confirmar-eliminacion', [
            'titulo' => 'Eliminar carrera',
            'nombre' => $carrera->nombre,
            'resumen' => $resumen,
            'requiereConfirmacion' => $this->hayDatosAfectados($resumen),
            'action' => route('admin.carreras.destroy', $carrera),
            'volver' => route('admin.carreras.index'),
        ]);
    }

    public function destroy(Request $request, Carrera $carrera)
    {
        $resumen = $this->resumenCarrera($carrera);

        if ($this->hayDatosAfectados($resumen) && ! $this->confirmacionValida($request)) {
            return redirect()
                ->route('admin.carreras.delete', $carrera)
                ->withErrors(['confirmacion' => 'Tenés que escribir ELIMINAR para confirmar.']);
        }

        // Guardamos las rutas de los archivos antes de que la cascada borre las filas
        $archivos = Programa::whereIn('materia_id', $carrera->materias()->pluck('id'))
            ->pluck('archivo')
            ->all();

        $carrera->delete();

        Storage::disk('public')->delete($archivos);

        return redirect()
            ->route('admin.carreras.index')
            ->with('success', 'Carrera eliminada correctamente.');
    }

    // ---------- Materias ----------

    public function storeMateria(Request $request, Carrera $carrera)
    {
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
        session(['ultimo_anio_id' => $validated['anio_id']]);

        return redirect()
            ->route('admin.carreras.show', $carrera)
            ->with('success', 'Materia agregada correctamente.');
    }

    public function editMateria(Materia $materia)
    {
        return view('admin.materias-edit', [
            'materia' => $materia->load('carrera'),
            'anios' => Anio::orderBy('id')->get(),
        ]);
    }

    public function updateMateria(Request $request, Materia $materia)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'anio_id' => ['required', 'exists:anios,id'],
        ]);

        $materia->update([
            'nombre' => trim($validated['nombre']),
            'anio_id' => $validated['anio_id'],
        ]);

        return redirect()
            ->route('admin.carreras.show', $materia->carrera_id)
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function confirmarEliminacionMateria(Materia $materia)
    {
        $resumen = $this->resumenMateria($materia);

        return view('admin.confirmar-eliminacion', [
            'titulo' => 'Eliminar materia',
            'nombre' => $materia->nombre,
            'resumen' => $resumen,
            'requiereConfirmacion' => $this->hayDatosAfectados($resumen),
            'action' => route('admin.materias.destroy', $materia),
            'volver' => route('admin.carreras.show', $materia->carrera_id),
        ]);
    }

    public function destroyMateria(Request $request, Materia $materia)
    {
        $resumen = $this->resumenMateria($materia);

        if ($this->hayDatosAfectados($resumen) && ! $this->confirmacionValida($request)) {
            return redirect()
                ->route('admin.materias.delete', $materia)
                ->withErrors(['confirmacion' => 'Tenés que escribir ELIMINAR para confirmar.']);
        }

        $carreraId = $materia->carrera_id;

        $archivos = Programa::where('materia_id', $materia->id)->pluck('archivo')->all();

        $materia->delete();

        Storage::disk('public')->delete($archivos);

        return redirect()
            ->route('admin.carreras.show', $carreraId)
            ->with('success', 'Materia eliminada correctamente.');
    }

    // ---------- Helpers de validación ----------

    private function resumenCarrera(Carrera $carrera): array
    {
        $materiaIds = $carrera->materias()->pluck('id')->all();

        return [
            [
                'label' => 'materias',
                'count' => count($materiaIds),
            ],
            [
                'label' => 'asignaciones de profesores a esas materias',
                'count' => DB::table('profesor_materias')->whereIn('materia_id', $materiaIds)->count(),
            ],
            [
                'label' => 'programas cargados (también se borran sus archivos)',
                'count' => Programa::whereIn('materia_id', $materiaIds)->count(),
            ],
            [
                'label' => 'asignaciones de EMTP a esta carrera',
                'count' => DB::table('emtp_carreras')->where('carrera_id', $carrera->id)->count(),
            ],
            [
                'label' => 'cursos asignados a preceptores',
                'count' => DB::table('preceptor_cursos')->where('carrera_id', $carrera->id)->count(),
            ],
        ];
    }

    private function resumenMateria(Materia $materia): array
    {
        return [
            [
                'label' => 'profesores asignados a esta materia',
                'count' => DB::table('profesor_materias')->where('materia_id', $materia->id)->count(),
            ],
            [
                'label' => 'programas cargados (también se borran sus archivos)',
                'count' => Programa::where('materia_id', $materia->id)->count(),
            ],
        ];
    }

    private function hayDatosAfectados(array $resumen): bool
    {
        return collect($resumen)->sum('count') > 0;
    }

    private function confirmacionValida(Request $request): bool
    {
        return mb_strtoupper(trim((string) $request->input('confirmacion'))) === self::PALABRA_CONFIRMACION;
    }
}