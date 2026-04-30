<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use App\Models\Servicio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public function with()
    {
        $user = Auth::user();
        $query = SolicitudMantenimiento::query();

        if ($user->rol !== 'supervisor') {
            $query->where('unidad_id', $user->unidad_id);
        }

        // Requests by Status
        $byStatus = (clone $query)
            ->select('estatus', DB::raw('count(*) as total'))
            ->groupBy('estatus')
            ->pluck('total', 'estatus')
            ->toArray();

        // Requests by Service
        $byService = (clone $query)
            ->join('servicios', 'solicitudes_mantenimiento.servicio_id', '=', 'servicios.id')
            ->select('servicios.nombre', DB::raw('count(*) as total'))
            ->groupBy('servicios.nombre')
            ->pluck('total', 'nombre')
            ->toArray();

        return [
            'statusData' => [
                'labels' => array_keys($byStatus),
                'values' => array_values($byStatus),
            ],
            'serviceData' => [
                'labels' => array_keys($byService),
                'values' => array_values($byService),
            ],
        ];
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
    <!-- Chart: Status -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-4">Solicitudes por Estatus</h4>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Chart: Services -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-4">Solicitudes por Servicio</h4>
        <div class="h-64">
            <canvas id="serviceChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            initCharts();
        });

        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
        });

        function initCharts() {
            Chart.defaults.color = '#9CA3AF';
            Chart.defaults.borderColor = '#374151';
            
            const statusCtx = document.getElementById('statusChart');
            const serviceCtx = document.getElementById('serviceChart');

            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($statusData['labels']),
                        datasets: [{
                            data: @json($statusData['values']),
                            backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#EF4444', '#6B7280'],
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            if (serviceCtx) {
                new Chart(serviceCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($serviceData['labels']),
                        datasets: [{
                            label: 'Total',
                            data: @json($serviceData['values']),
                            backgroundColor: '#6366F1',
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        }
    </script>
</div>
