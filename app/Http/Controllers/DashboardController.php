<?php

namespace App\Http\Controllers;

use App\Models\Profesor;

class DashboardController extends Controller
{
    public function index()
    {
        $roles = auth()->user()->roles_ids;

        // El orden de esta lista es la prioridad: gana el primer rol que tenga el usuario
        $destinos = [
            Profesor::ROL_ADMIN => 'admin.programas.index',
            Profesor::ROL_DIRECTIVO => 'consulta.programas.index',
            Profesor::ROL_JEFE => 'jefatura.perfiles.index',
            Profesor::ROL_EMTP => 'consulta.programas.index',
            Profesor::ROL_PRECEPTOR => 'consulta.programas.index',
            Profesor::ROL_PROFESOR => 'profesor.programas.index',
        ];

        foreach ($destinos as $rolId => $ruta) {
            if (in_array($rolId, $roles)) {
                return redirect()->route($ruta);
            }
        }

        // Sin ningún rol asignado: aviso de espera
        return view('dashboard', [
            'sinRol' => true,
        ]);
    }
}