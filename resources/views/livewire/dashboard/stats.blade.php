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

        // SLA Compliance: Finished within deadline or Open within deadline
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

        return [
            'stats' => $stats,
        ];
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <!-- Card Total -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-indigo-500">
        <div class="text-xs font-bold text-indigo-500 uppercase">Total Solicitudes</div>
        <div class="text-2xl font-bold dark:text-white">{{ $stats['total'] }}</div>
    </div>

    <!-- Card Abiertos -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-green-500">
        <div class="text-xs font-bold text-green-500 uppercase">Abiertas / Pendientes</div>
        <div class="text-2xl font-bold dark:text-white">{{ $stats['abiertos'] }}</div>
    </div>

    <!-- Card En Proceso -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-blue-500">
        <div class="text-xs font-bold text-blue-500 uppercase">En Proceso</div>
        <div class="text-2xl font-bold dark:text-white">{{ $stats['proceso'] }}</div>
    </div>

    <!-- Card Terminados -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-red-500">
        <div class="text-xs font-bold text-red-500 uppercase">Terminadas</div>
        <div class="text-2xl font-bold dark:text-white">{{ $stats['terminados'] }}</div>
    </div>

    <!-- Card SLA -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-orange-500">
        <div class="text-xs font-bold text-orange-500 uppercase">Cumplimiento SLA</div>
        <div class="text-2xl font-bold dark:text-white">{{ $stats['sla'] }}%</div>
    </div>
</div>
