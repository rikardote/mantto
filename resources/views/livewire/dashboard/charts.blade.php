<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
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

        $byStatus = (clone $query)
            ->select('estatus', DB::raw('count(*) as total'))
            ->groupBy('estatus')
            ->pluck('total', 'estatus')
            ->toArray();

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
    <!-- Status Chart -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Solicitudes por Estatus</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Distribución actual</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                </svg>
            </div>
        </div>
        <div class="h-64 relative">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Service Chart -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-500 to-blue-500"></div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Solicitudes por Servicio</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Por tipo de servicio</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <div class="h-64 relative">
            <canvas id="serviceChart"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:navigated', () => initCharts());
    document.addEventListener('DOMContentLoaded', () => initCharts());

    function initCharts() {
        Chart.defaults.font.family = "'Figtree', sans-serif";
        
        const statusCtx = document.getElementById('statusChart');
        const serviceCtx = document.getElementById('serviceChart');

        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($statusData['labels']),
                    datasets: [{
                        data: @json($statusData['values']),
                        backgroundColor: [
                            '#10B981',
                            '#F59E0B', 
                            '#3B82F6',
                            '#EF4444',
                            '#8B5CF6',
                            '#6B7280'
                        ],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    }
                }
            });
        }

        if (serviceCtx) {
            const gradient = serviceCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, '#6366F1');
            gradient.addColorStop(1, '#8B5CF6');

            new Chart(serviceCtx, {
                type: 'bar',
                data: {
                    labels: @json($serviceData['labels']),
                    datasets: [{
                        label: 'Total',
                        data: @json($serviceData['values']),
                        backgroundColor: gradient,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { padding: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            },
                            ticks: { stepSize: 1, padding: 10 }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    }
</script>