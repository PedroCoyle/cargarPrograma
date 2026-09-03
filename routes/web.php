<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminUsuariosController;
use App\Http\Controllers\Profesor\MateriasController;
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
        });

    // Rutas exclusivas de profesor
    Route::middleware('role:' . Profesor::ROL_PROFESOR)
        ->prefix('profesor')
        ->name('profesor.')
        ->group(function () {
            Route::get('/materias', [MateriasController::class, 'index'])->name('materias');
        });

});