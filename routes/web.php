<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminPerfilesController;
use App\Http\Controllers\Admin\AdminUsuariosController;
use App\Http\Controllers\Admin\AdminProgramasController;
use App\Http\Controllers\Admin\AdminAsignacionesController;
use App\Http\Controllers\Admin\CarrerasController;
use App\Http\Controllers\Consulta\ProgramasConsultaController;
use App\Http\Controllers\Jefatura\JefaturaAsignacionesController;
use App\Http\Controllers\Jefatura\JefaturaCarrerasController;
use App\Http\Controllers\Jefatura\JefaturaPerfilesController;
use App\Http\Controllers\Jefatura\JefaturaUsuariosController;
use App\Http\Controllers\Jefatura\JefaturaProgramasController;
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

            // Perfiles
            Route::get('/perfiles', [AdminPerfilesController::class, 'index'])->name('perfiles.index');
            Route::post('/perfiles', [AdminPerfilesController::class, 'store'])->name('perfiles.store');
            Route::get('/perfiles/{profesor}/editar', [AdminPerfilesController::class, 'edit'])->name('perfiles.edit');
            Route::put('/perfiles/{profesor}', [AdminPerfilesController::class, 'update'])->name('perfiles.update');
            Route::get('/perfiles/{profesor}/eliminar', [AdminPerfilesController::class, 'confirmarEliminacion'])->name('perfiles.delete');
            Route::delete('/perfiles/{profesor}', [AdminPerfilesController::class, 'destroy'])->name('perfiles.destroy');

            // Asignaciones (se entra desde la lista de Perfiles)
            Route::get('/perfiles/{profesor}/materias', [AdminAsignacionesController::class, 'materias'])->name('profesores.materias');
            Route::post('/perfiles/{profesor}/materias', [AdminAsignacionesController::class, 'updateMaterias'])->name('profesores.materias.update');
            Route::get('/perfiles/{profesor}/carreras', [AdminAsignacionesController::class, 'carreras'])->name('profesores.carreras');
            Route::post('/perfiles/{profesor}/carreras', [AdminAsignacionesController::class, 'updateCarreras'])->name('profesores.carreras.update');
            Route::get('/perfiles/{profesor}/cursos', [AdminAsignacionesController::class, 'cursos'])->name('profesores.cursos');
            Route::post('/perfiles/{profesor}/cursos', [AdminAsignacionesController::class, 'updateCursos'])->name('profesores.cursos.update');
            Route::get('/perfiles/{profesor}/area', [AdminAsignacionesController::class, 'areas'])->name('profesores.areas');
            Route::post('/perfiles/{profesor}/area', [AdminAsignacionesController::class, 'updateAreas'])->name('profesores.areas.update');

            // Usuarios y roles (cuentas)
            Route::get('/usuarios', [AdminUsuariosController::class, 'index'])->name('usuarios');
            Route::get('/usuarios/{user}/asociar', [AdminUsuariosController::class, 'asociar'])->name('usuarios.associate');
            Route::post('/usuarios/{user}/asociar', [AdminUsuariosController::class, 'asociarStore'])->name('usuarios.associate.store');
            Route::post('/usuarios/{user}/desvincular', [AdminUsuariosController::class, 'desvincular'])->name('usuarios.unlink');
            Route::delete('/usuarios/{user}', [AdminUsuariosController::class, 'destroy'])->name('usuarios.destroy');

            // Programas
            Route::get('/programas', [AdminProgramasController::class, 'index'])->name('programas.index');
            Route::get('/programas/carreras/{carrera}', [AdminProgramasController::class, 'show'])->name('programas.carrera');
            Route::get('/programas/historial/{profesor}/{materia}', [AdminProgramasController::class, 'historial'])->name('programas.historial');
            Route::delete('/programas/{programa}', [AdminProgramasController::class, 'destroy'])->name('programas.destroy');

            // Carreras y materias
            Route::get('/carreras', [CarrerasController::class, 'index'])->name('carreras.index');
            Route::post('/carreras', [CarrerasController::class, 'store'])->name('carreras.store');
            Route::get('/carreras/{carrera}', [CarrerasController::class, 'show'])->name('carreras.show');
            Route::get('/carreras/{carrera}/editar', [CarrerasController::class, 'edit'])->name('carreras.edit');
            Route::put('/carreras/{carrera}', [CarrerasController::class, 'update'])->name('carreras.update');
            Route::get('/carreras/{carrera}/eliminar', [CarrerasController::class, 'confirmarEliminacion'])->name('carreras.delete');
            Route::delete('/carreras/{carrera}', [CarrerasController::class, 'destroy'])->name('carreras.destroy');

            Route::post('/carreras/{carrera}/materias', [CarrerasController::class, 'storeMateria'])->name('carreras.materias.store');
            Route::get('/materias/{materia}/editar', [CarrerasController::class, 'editMateria'])->name('materias.edit');
            Route::put('/materias/{materia}', [CarrerasController::class, 'updateMateria'])->name('materias.update');
            Route::get('/materias/{materia}/eliminar', [CarrerasController::class, 'confirmarEliminacionMateria'])->name('materias.delete');
            Route::delete('/materias/{materia}', [CarrerasController::class, 'destroyMateria'])->name('materias.destroy');
        });

    // Jefe de área / departamento
    Route::middleware('role:' . Profesor::ROL_JEFE)
        ->prefix('jefatura')
        ->name('jefatura.')
        ->group(function () {

            // Perfiles de su área
            Route::get('/perfiles', [JefaturaPerfilesController::class, 'index'])->name('perfiles.index');
            Route::post('/perfiles', [JefaturaPerfilesController::class, 'store'])->name('perfiles.store');
            Route::get('/perfiles/{profesor}/editar', [JefaturaPerfilesController::class, 'edit'])->name('perfiles.edit');
            Route::put('/perfiles/{profesor}', [JefaturaPerfilesController::class, 'update'])->name('perfiles.update');

            // Asignaciones dentro de su área
            Route::get('/perfiles/{profesor}/materias', [JefaturaAsignacionesController::class, 'materias'])->name('perfiles.materias');
            Route::post('/perfiles/{profesor}/materias', [JefaturaAsignacionesController::class, 'updateMaterias'])->name('perfiles.materias.update');
            Route::get('/perfiles/{profesor}/carreras', [JefaturaAsignacionesController::class, 'carreras'])->name('perfiles.carreras');
            Route::post('/perfiles/{profesor}/carreras', [JefaturaAsignacionesController::class, 'updateCarreras'])->name('perfiles.carreras.update');
            Route::get('/perfiles/{profesor}/cursos', [JefaturaAsignacionesController::class, 'cursos'])->name('perfiles.cursos');
            Route::post('/perfiles/{profesor}/cursos', [JefaturaAsignacionesController::class, 'updateCursos'])->name('perfiles.cursos.update');

            // Cuentas: asociar con un perfil
            Route::get('/usuarios', [JefaturaUsuariosController::class, 'index'])->name('usuarios');
            Route::get('/usuarios/{user}/asociar', [JefaturaUsuariosController::class, 'asociar'])->name('usuarios.associate');
            Route::post('/usuarios/{user}/asociar', [JefaturaUsuariosController::class, 'asociarStore'])->name('usuarios.associate.store');
            Route::post('/usuarios/{user}/desvincular', [JefaturaUsuariosController::class, 'desvincular'])->name('usuarios.unlink');

            // Carreras y materias de su área (sin eliminar)
            Route::get('/carreras', [JefaturaCarrerasController::class, 'index'])->name('carreras.index');
            Route::get('/carreras/{carrera}', [JefaturaCarrerasController::class, 'show'])->name('carreras.show');
            Route::post('/carreras/{carrera}/materias', [JefaturaCarrerasController::class, 'storeMateria'])->name('carreras.materias.store');
            Route::get('/materias/{materia}/editar', [JefaturaCarrerasController::class, 'editMateria'])->name('materias.edit');
            Route::put('/materias/{materia}', [JefaturaCarrerasController::class, 'updateMateria'])->name('materias.update');

            // Programas cargados de su área
Route::get('/programas', [JefaturaProgramasController::class, 'index'])->name('programas.index');
Route::get('/programas/carreras/{carrera}', [JefaturaProgramasController::class, 'show'])->name('programas.carrera');
Route::get('/programas/historial/{profesor}/{materia}', [JefaturaProgramasController::class, 'historial'])->name('programas.historial');
Route::delete('/programas/{programa}', [JefaturaProgramasController::class, 'destroy'])->name('programas.destroy');
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