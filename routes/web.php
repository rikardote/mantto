<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

use App\Http\Controllers\SolicitudExportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('solicitudes/exportar', [SolicitudExportController::class, 'export'])->name('solicitudes.exportar');
    Route::get('solicitudes/{solicitud}/imprimir', function (App\Models\SolicitudMantenimiento $solicitud) {
        return view('solicitudes.print', compact('solicitud'));
    })->name('solicitudes.imprimir');
    Route::view('solicitudes', 'solicitudes.index')->name('solicitudes.index');
    Route::view('solicitudes/crear', 'solicitudes.create')->name('solicitudes.create');
    Route::get('solicitudes/{solicitud}', function (App\Models\SolicitudMantenimiento $solicitud) {
        return view('solicitudes.show', ['solicitud' => $solicitud]);
    })->name('solicitudes.show');
    Route::get('solicitudes/{solicitud}/editar', function (App\Models\SolicitudMantenimiento $solicitud) {
        return view('solicitudes.edit', ['solicitud' => $solicitud]);
    })->name('solicitudes.edit');

    Route::get('usuarios', function () {
        if (auth()->user()->rol !== 'supervisor') abort(403);
        return view('usuarios.index');
    })->name('usuarios.index');

    Route::get('catalogos', function () {
        if (auth()->user()->rol !== 'supervisor') abort(403);
        return view('catalogos.index');
    })->name('catalogos.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
