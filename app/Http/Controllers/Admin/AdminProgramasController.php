<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProgramasController extends Controller
{
    public function index(Request $request)
    {
        $carreras = Carrera::orderBy('nombre')->get();

        $busqueda = $request->query('buscar');
        $carreraId = $request->query('carrera_id');

        $query = Programa::with(['profesor.user', 'materia.carrera', 'materia.anio'])
            ->orderByDesc('fecha_subida');

        if ($busqueda) {
            $query->whereHas('profesor', function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhereHas('user', function ($q2) use ($busqueda) {
                      $q2->where('email', 'like', "%{$busqueda}%")
                         ->orWhere('nombre', 'like', "%{$busqueda}%");
                  });
            });
        }

        if ($carreraId) {
            $query->whereHas('materia', function ($q) use ($carreraId) {
                $q->where('carrera_id', $carreraId);
            });
        }

        $programas = $query->paginate(20)->withQueryString();

        return view('admin.programas-index', [
            'programas' => $programas,
            'carreras' => $carreras,
            'busqueda' => $busqueda,
            'carreraId' => $carreraId,
        ]);
    }

    public function destroy(Programa $programa)
    {
        Storage::disk('public')->delete($programa->archivo);

        $programa->delete();

        return redirect()
            ->route('admin.programas.index')
            ->with('success', 'Programa eliminado correctamente.');
    }
}