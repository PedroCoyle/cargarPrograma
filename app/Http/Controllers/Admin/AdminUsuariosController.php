<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anio;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\Http\Request;

class AdminUsuariosController extends Controller
{
    public function profesores(Request $request)
{
    $query = Profesor::with('user');

    $sinRol = $request->boolean('sin_rol');

    if ($sinRol) {
        $query->whereDoesntHave('rolesPivot');
    }

    $profesores = $query->get();

    return view('admin.profesores', [
        'profesores' => $profesores,
        'sinRol' => $sinRol,
    ]);
}

public function edit(Profesor $profesor)
{
    return view('admin.profesores-edit', [
        'profesor' => $profesor,
    ]);
}

public function update(Request $request, Profesor $profesor)
{
    $validated = $request->validate([
        'nombre' => ['required', 'string', 'max:255'],
        'roles' => ['required', 'array', 'min:1'],
        'roles.*' => ['in:' . implode(',', [
            Profesor::ROL_PROFESOR,
            Profesor::ROL_PRECEPTOR,
            Profesor::ROL_ADMIN,
            Profesor::ROL_EMTP,
            Profesor::ROL_DIRECTIVO,
        ])],
    ]);

    $profesor->update(['nombre' => $validated['nombre']]);
    $profesor->asignarRoles($validated['roles']);

    return redirect()
        ->route('admin.profesores')
        ->with('success', 'Roles asignados correctamente.');
}

    public function destroy(Profesor $profesor)
    {
        $user = $profesor->user;

        $profesor->delete();
        $user?->delete();

        return redirect()
            ->route('admin.profesores')
            ->with('success', 'Usuario eliminado correctamente.');
    }

public function materias(Request $request, Profesor $profesor)
{
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

 public function materiasIndex()
{
    $profesores = Profesor::with('user')
        ->whereHas('rolesPivot', function ($q) {
            $q->whereIn('rol_id', [
                Profesor::ROL_PROFESOR,
                Profesor::ROL_PRECEPTOR,
                Profesor::ROL_EMTP,
            ]);
        })
        ->get();

    return view('admin.materias-index', [
        'profesores' => $profesores,
    ]);
}
}