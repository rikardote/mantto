<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function with()
    {
        $user = Auth::user();
        $query = SolicitudMantenimiento::query();

        if ($user->rol !== 'supervisor') {
            $query->where('unidad_id', $user->unidad_id);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'abiertos' => (clone $query)->where('estatus', 'abierto')->count(),
            'proceso' => (clone $query)->where('estatus', 'en_proceso')->count(),
            'terminados' => (clone $query)->where('estatus', 'terminado')->count(),
        ];

        $totalForSla = (clone $query)->whereNotNull('fecha_limite')->count();
        $withinSla = (clone $query)->whereNotNull('fecha_limite')
            ->where(function($q) {
                $q->where(function($sq) {
                    $sq->where('estatus', 'terminado')
                       ->whereColumn('fecha_cierre', '<=', 'fecha_limite');
                })->orWhere(function($sq) {
                    $sq->where('estatus', '!=', 'terminado')
                       ->where('fecha_limite', '>', now());
                });
            })->count();

        $stats['sla'] = $totalForSla > 0 ? round(($withinSla / $totalForSla) * 100) : 100;

        return ['stats' => $stats];
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-5 gap-6">
    <!-- Total Solicitudes -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-100 dark:bg-indigo-900/30 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Solicitudes</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
    </div>

    <!-- Abiertos -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-100 dark:bg-emerald-900/30 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Abiertas</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['abiertos'] }}</p>
        </div>
    </div>

    <!-- En Proceso -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-100 dark:bg-amber-900/30 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">En Proceso</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['proceso'] }}</p>
        </div>
    </div>

    <!-- Terminados -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-100 dark:bg-rose-900/30 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terminadas</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['terminados'] }}</p>
        </div>
    </div>

    <!-- SLA -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-xl transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-violet-100 dark:bg-violet-900/30 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cumplimiento SLA</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['sla'] }}%</p>
        </div>
    </div>
</div>