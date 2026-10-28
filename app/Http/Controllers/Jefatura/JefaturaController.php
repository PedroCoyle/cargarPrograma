<?php

namespace App\Http\Controllers\Jefatura;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Profesor;

abstract class JefaturaController extends Controller
{
    protected function jefe(): Profesor
    {
        return auth()->user()->profesor;
    }

    protected function carrerasIds(): array
    {
        return $this->jefe()->carrerasJefe()->pluck('carreras.id')->all();
    }

    protected function carrerasDelJefe()
    {
        return Carrera::whereIn('id', $this->carrerasIds())->orderBy('nombre')->get();
    }

    // Solo perfiles de su área y sin roles Admin, Directivo ni Jefe
    protected function perfil(Profesor $profesor): Profesor
    {
        abort_unless($profesor->esGestionablePor($this->jefe()), 404);

        return $profesor;
    }
}