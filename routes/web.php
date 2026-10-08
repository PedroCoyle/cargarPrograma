<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminUsuariosController;
use App\Http\Controllers\Admin\AdminProgramasController;
use App\Http\Controllers\Admin\AdminAsignacionesController;
use App\Http\Controllers\Admin\CarrerasController;
use App\Http\Controllers\Consulta\ProgramasConsultaController;
use App\Http\Controllers\Profesor\ProgramasController;
use App\Models\Profesor;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {

    // Página única a la que llega cualquier usuario logueado
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas exclusivas de admin
    Route::middleware('role:' . Profesor::ROL_ADMIN)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/usuarios', [AdminUsuariosController::class, 'index'])->name('usuarios');
            Route::get('/profesores', [AdminUsuariosController::class, 'profesores'])->name('profesores');

            Route::get('/profesores/{profesor}/editar', [AdminUsuariosController::class, 'edit'])->name('profesores.edit');
            Route::put('/profesores/{profesor}', [AdminUsuariosController::class, 'update'])->name('profesores.update');
            Route::delete('/profesores/{profesor}', [AdminUsuariosController::class, 'destroy'])->name('profesores.destroy');

            Route::get('/profesores/{profesor}/materias', [AdminUsuariosController::class, 'materias'])->name('profesores.materias');
            Route::post('/profesores/{profesor}/materias', [AdminUsuariosController::class, 'updateMaterias'])->name('profesores.materias.update');
            Route::get('/materias', [AdminUsuariosController::class, 'materiasIndex'])->name('profesores.materias.index');

            Route::get('/profesores/{profesor}/carreras', [AdminAsignacionesController::class, 'carreras'])->name('profesores.carreras');
            Route::post('/profesores/{profesor}/carreras', [AdminAsignacionesController::class, 'updateCarreras'])->name('profesores.carreras.update');
            Route::get('/profesores/{profesor}/cursos', [AdminAsignacionesController::class, 'cursos'])->name('profesores.cursos');
            Route::post('/profesores/{profesor}/cursos', [AdminAsignacionesController::class, 'updateCursos'])->name('profesores.cursos.update');

            Route::get('/programas', [AdminProgramasController::class, 'index'])->name('programas.index');
            Route::get('/programas/carreras/{carrera}', [AdminProgramasController::class, 'show'])->name('programas.carrera');
            Route::get('/programas/historial/{profesor}/{materia}', [AdminProgramasController::class, 'historial'])->name('programas.historial');
            Route::delete('/programas/{programa}', [AdminProgramasController::class, 'destroy'])->name('programas.destroy');

            Route::get('/carreras', [CarrerasController::class, 'index'])->name('carreras.index');
            Route::post('/carreras', [CarrerasController::class, 'store'])->name('carreras.store');
            Route::get('/carreras/{carrera}', [CarrerasController::class, 'show'])->name('carreras.show');
            Route::delete('/carreras/{carrera}', [CarrerasController::class, 'destroy'])->name('carreras.destroy');

            Route::post('/carreras/{carrera}/materias', [CarrerasController::class, 'storeMateria'])->name('carreras.materias.store');
            Route::delete('/materias/{materia}', [CarrerasController::class, 'destroyMateria'])->name('materias.destroy');
        });

    // Consulta de programas (solo lectura): preceptor, EMTP y directivo
    Route::middleware('role:' . Profesor::ROL_PRECEPTOR . ',' . Profesor::ROL_EMTP . ',' . Profesor::ROL_DIRECTIVO)
        ->prefix('consulta')
        ->name('consulta.')
        ->group(function () {
            Route::get('/programas', [ProgramasConsultaController::class, 'index'])->name('programas.index');
            Route::get('/programas/carreras/{carrera}', [ProgramasConsultaController::class, 'show'])->name('programas.carrera');
        });

    // Historial: solo directivo
    Route::middleware('role:' . Profesor::ROL_DIRECTIVO)
        ->prefix('consulta')
        ->name('consulta.')
        ->group(function () {
            Route::get('/programas/historial/{profesor}/{materia}', [ProgramasConsultaController::class, 'historial'])->name('programas.historial');
        });

    // Rutas exclusivas de profesor
    Route::middleware('role:' . Profesor::ROL_PROFESOR)
        ->prefix('profesor')
        ->name('profesor.')
        ->group(function () {
            Route::get('/programas', [ProgramasController::class, 'index'])->name('programas.index');
            Route::get('/programas/{materia}/subir', [ProgramasController::class, 'create'])->name('programas.create');
            Route::post('/programas/{materia}', [ProgramasController::class, 'store'])->name('programas.store');
        });

});
