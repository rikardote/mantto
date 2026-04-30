<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use App\Models\Unidad;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $status = '';
    public $unidad_id = '';
    public $search = '';

    public function updating($property)
    {
        if (in_array($property, ['status', 'unidad_id', 'search'])) {
            $this->resetPage();
        }
    }

    public function with()
    {
        $user = Auth::user();
        $query = SolicitudMantenimiento::with(['unidad', 'servicio', 'prioridad'])
            ->latest();

        if ($user->rol !== 'supervisor') {
            $query->where('unidad_id', $user->unidad_id);
        } elseif ($this->unidad_id) {
            $query->where('unidad_id', $this->unidad_id);
        }

        if ($this->status) {
            $query->where('estatus', $this->status);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('folio_oficio', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'solicitudes' => $query->paginate(10),
            'unidades' => $user->rol === 'supervisor' ? Unidad::all() : [],
        ];
    }
}; ?>

<div>
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <x-text-input wire:model.live="search" class="w-full" placeholder="Buscar por título o folio..." />
        </div>
        <div class="w-full md:w-48">
            <select wire:model.live="status" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Todos los estatus</option>
                <option value="abierto">Abierto</option>
                <option value="validado">Validado</option>
                <option value="asignado">Asignado</option>
                <option value="en_proceso">En Proceso</option>
                <option value="terminado">Terminado</option>
            </select>
        </div>
        @if(Auth::user()->rol === 'supervisor')
            <div class="w-full md:w-64">
                <select wire:model.live="unidad_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">Todas las unidades</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Folio / Título</th>
                    <th class="px-6 py-3">Unidad</th>
                    <th class="px-6 py-3">Servicio</th>
                    <th class="px-6 py-3">Prioridad</th>
                    <th class="px-6 py-3">Estatus</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitudes as $s)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            <div class="text-xs text-gray-400">{{ $s->folio_oficio ?? 'Sin folio' }}</div>
                            <div>{{ $s->titulo }}</div>
                        </td>
                        <td class="px-6 py-4">{{ $s->unidad->nombre }}</td>
                        <td class="px-6 py-4">{{ $s->servicio->nombre }}</td>
                        <td class="px-6 py-4">
                            @php
                                $priorityColor = match($s->prioridad->nombre) {
                                    'Alta' => 'text-red-600 bg-red-100',
                                    'Media' => 'text-yellow-600 bg-yellow-100',
                                    'Baja' => 'text-blue-600 bg-blue-100',
                                    default => 'text-gray-600 bg-gray-100',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColor }}">
                                {{ $s->prioridad->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColor = match($s->estatus) {
                                    'abierto' => 'bg-green-100 text-green-800',
                                    'validado' => 'bg-yellow-100 text-yellow-800',
                                    'asignado' => 'bg-blue-100 text-blue-800',
                                    'en_proceso' => 'bg-orange-100 text-orange-800',
                                    'terminado' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($s->estatus) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ $s->fecha_solicitud->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('solicitudes.show', $s) }}" class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                            No se encontraron solicitudes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>
</div>
