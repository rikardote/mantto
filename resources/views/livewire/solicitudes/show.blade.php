<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use App\Models\Avance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use App\Notifications\SolicitudActualizada;

new class extends Component {
    use WithFileUploads;

    public SolicitudMantenimiento $solicitud;
    public $orden_servicio = '';
    public $comentario_avance = '';
    public $porcentaje_avance = 0;
    public $archivo_avance;

    public function mount(SolicitudMantenimiento $solicitud)
    {
        $this->solicitud = $solicitud->load(['unidad', 'servicio', 'prioridad', 'tipoMantenimiento', 'creador', 'avances.user']);
        $this->orden_servicio = $solicitud->orden_servicio;
    }

    public function cambiarEstatus($nuevoEstatus)
    {
        $this->authorize('changeStatus', $this->solicitud);

        $updateData = ['estatus' => $nuevoEstatus];

        if ($nuevoEstatus === 'en_proceso' && !$this->solicitud->fecha_atencion) {
            $updateData['fecha_atencion'] = now();
        }

        if ($nuevoEstatus === 'terminado') {
            $updateData['fecha_cierre'] = now();
        }

        $this->solicitud->update($updateData);
        $this->solicitud->refresh();

        // Notificar a todos los usuarios de la Unidad
        $usuariosUnidad = User::where('unidad_id', $this->solicitud->unidad_id)->get();
        foreach ($usuariosUnidad as $user) {
            if ($user->id !== Auth::id()) {
                $user->notify(new SolicitudActualizada($this->solicitud, "Tu solicitud ha cambiado a: " . strtoupper($nuevoEstatus)));
            }
        }
        
        session()->flash('status', "Estatus actualizado a: $nuevoEstatus");
    }

    public function guardarOrdenServicio()
    {
        $this->authorize('update', $this->solicitud);
        $this->validate(['orden_servicio' => 'required|string|max:255']);

        $this->solicitud->update([
            'orden_servicio' => $this->orden_servicio,
            'estatus' => $this->solicitud->estatus === 'validado' ? 'asignado' : $this->solicitud->estatus
        ]);

        if ($this->solicitud->creador) {
            $usuariosUnidad = User::where('unidad_id', $this->solicitud->unidad_id)->get();
            foreach ($usuariosUnidad as $user) {
                if ($user->id !== Auth::id()) {
                    $user->notify(new SolicitudActualizada($this->solicitud, "Se ha asignado la Orden de Servicio: " . $this->orden_servicio));
                }
            }
        }
        
        session()->flash('status', 'Orden de servicio guardada.');
    }

    public function agregarAvance()
    {
        $this->validate([
            'comentario_avance' => 'required|string',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'archivo_avance' => 'nullable|file|max:5120', // 5MB
        ]);

        $path = null;
        if ($this->archivo_avance) {
            $path = $this->archivo_avance->store('evidencias', 'public');
        }

        Avance::create([
            'solicitud_id' => $this->solicitud->id,
            'user_id' => Auth::id(),
            'comentario' => $this->comentario_avance,
            'porcentaje' => $this->porcentaje_avance,
            'fecha' => now(),
            'archivo_path' => $path,
        ]);

        $this->reset(['comentario_avance', 'archivo_avance']);
        $this->solicitud->refresh();
        session()->flash('status', 'Avance registrado.');
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg shadow-sm text-sm font-bold">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 md:p-6">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4">
                <div class="w-full">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ $solicitud->titulo }}</h3>
                    <p class="text-xs md:text-sm text-gray-500 mt-1 uppercase font-semibold">{{ $solicitud->servicio->nombre }} • {{ $solicitud->tipoMantenimiento->nombre }}</p>
                </div>
                <div class="w-full md:w-auto">
                    <span class="block text-center px-3 py-1 rounded-full text-[10px] md:text-xs font-bold uppercase tracking-widest
                        {{ $solicitud->estatus === 'abierto' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $solicitud->estatus === 'terminado' ? 'bg-red-100 text-red-800' : '' }}
                        {{ in_array($solicitud->estatus, ['validado', 'asignado', 'en_proceso']) ? 'bg-blue-100 text-blue-800' : '' }}">
                        {{ $solicitud->estatus }}
                    </span>
                </div>
            </div>

            <div class="prose dark:prose-invert max-w-none mb-6">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $solicitud->descripcion }}</p>
                @if($solicitud->descripcion_servicio_otro)
                    <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded text-sm italic">
                        <strong>Específico:</strong> {{ $solicitud->descripcion_servicio_otro }}
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm border-t dark:border-gray-700 pt-4">
                <div>
                    <span class="text-gray-500">Unidad:</span>
                    <span class="font-medium dark:text-gray-200 block">{{ $solicitud->unidad->nombre }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Folio:</span>
                    <span class="font-medium dark:text-gray-200 block">{{ $solicitud->folio_oficio ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Solicitado por:</span>
                    <span class="font-medium dark:text-gray-200 block">{{ $solicitud->creador->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Fecha de Solicitud:</span>
                    <span class="font-medium dark:text-gray-200 block">{{ $solicitud->fecha_solicitud->format('d/m/Y H:i') }}</span>
                </div>
                @if($solicitud->archivo_oficio_path)
                    <div class="col-span-2 mt-2 p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded border border-indigo-100 dark:border-indigo-800">
                        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold uppercase block mb-1">Documento Inicial / Oficio</span>
                        <a href="#" 
                           @click.prevent="$dispatch('open-modal', { 
                                url: '{{ Storage::url($solicitud->archivo_oficio_path) }}', 
                                type: '{{ str_ends_with($solicitud->archivo_oficio_path, '.pdf') ? 'pdf' : 'image' }}' 
                           })"
                           class="text-sm text-indigo-700 dark:text-indigo-300 hover:underline flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Ver Archivo Adjunto (Vista Previa)
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Avances -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h4 class="text-lg font-semibold mb-4 dark:text-white">Avances y Seguimiento</h4>
            
            <div class="space-y-4 mb-6">
                @forelse($solicitud->avances as $avance)
                    <div class="flex gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-sm dark:text-indigo-400">{{ $avance->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $avance->fecha->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm dark:text-gray-300">{{ $avance->comentario }}</p>
                            
                            @if($avance->archivo_path)
                                <div class="mt-2">
                                    <a href="#" 
                                       @click.prevent="$dispatch('open-modal', { 
                                            url: '{{ Storage::url($avance->archivo_path) }}', 
                                            type: '{{ str_ends_with($avance->archivo_path, '.pdf') ? 'pdf' : 'image' }}' 
                                       })"
                                       class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Ver Evidencia (Vista Previa)
                                    </a>
                                </div>
                            @endif

                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $avance->porcentaje }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4 italic">No hay avances registrados.</p>
                @endforelse
            </div>

            @if(Auth::user()->can('update', $solicitud))
                <div class="border-t dark:border-gray-700 pt-4">
                    <h5 class="text-sm font-bold mb-3 dark:text-gray-200">Registrar Nuevo Avance</h5>
                    <div class="space-y-3">
                        <textarea wire:model="comentario_avance" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="Comentarios del progreso..."></textarea>
                        
                        <div class="flex flex-col gap-2">
                            <x-input-label for="archivo_avance" value="Adjuntar Evidencia (Foto/PDF)" />
                            <input type="file" wire:model="archivo_avance" id="archivo_avance" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <div wire:loading wire:target="archivo_avance" class="text-xs text-indigo-500 italic">Subiendo archivo...</div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Porcentaje de avance: {{ $porcentaje_avance }}%</label>
                                <input type="range" wire:model.live="porcentaje_avance" min="0" max="100" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <x-primary-button wire:click="agregarAvance">Registrar</x-primary-button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Info & Controls -->
    <div class="space-y-6">
        <!-- SLA Info -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h4 class="text-lg font-semibold mb-4 dark:text-white">SLA & Tiempos</h4>
            <div class="space-y-4">
                <div>
                    <span class="text-xs text-gray-500 block uppercase tracking-wider">Prioridad</span>
                    <span class="font-bold {{ $solicitud->prioridad->nombre === 'Alta' ? 'text-red-500' : 'text-indigo-500' }}">
                        {{ $solicitud->prioridad->nombre }} ({{ $solicitud->prioridad->tiempo_respuesta_horas }} hrs)
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block uppercase tracking-wider">Fecha Límite</span>
                    <span class="font-medium dark:text-gray-200">{{ $solicitud->fecha_limite->format('d/m/Y H:i') }}</span>
                    @if($solicitud->fecha_limite->isPast() && $solicitud->estatus !== 'terminado')
                        <span class="block text-xs text-red-500 font-bold uppercase mt-1">¡VENCIDA!</span>
                    @endif
                </div>
                @if($solicitud->fecha_atencion)
                    <div>
                        <span class="text-xs text-gray-500 block uppercase tracking-wider">Atendido en</span>
                        <span class="font-medium dark:text-gray-200">{{ $solicitud->fecha_atencion->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Workflow Controls -->
        @if(Auth::user()->can('update', $solicitud))
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 border-t-4 border-indigo-500">
                <h4 class="text-lg font-semibold mb-4 dark:text-white">Panel de Gestión</h4>
                
                <div class="space-y-4">
                    <div class="flex flex-col gap-3">
                        <div class="w-full">
                            <a href="{{ route('solicitudes.imprimir', $solicitud) }}" target="_blank" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Imprimir Ficha
                            </a>
                        </div>
                        @if($solicitud->estatus === 'abierto')
                            <x-primary-button wire:click="cambiarEstatus('validado')" class="justify-center w-full bg-yellow-600 hover:bg-yellow-700">1. Validar y Recibir</x-primary-button>
                        @endif

                        <div class="space-y-2 border-t dark:border-gray-700 pt-2">
                            <x-input-label for="orden_servicio" value="Orden de Servicio #" />
                            <div class="flex gap-2">
                                <x-text-input wire:model="orden_servicio" id="orden_servicio" class="flex-1 text-sm" placeholder="Ej: OS-123" />
                                <x-secondary-button wire:click="guardarOrdenServicio">OK</x-secondary-button>
                            </div>
                        </div>

                        @if(in_array($solicitud->estatus, ['validado', 'asignado', 'abierto']))
                            <x-primary-button wire:click="cambiarEstatus('en_proceso')" class="justify-center w-full bg-blue-600 hover:bg-blue-700">2. Marcar "En Proceso"</x-primary-button>
                        @endif

                        @if($solicitud->estatus === 'en_proceso')
                            <x-primary-button wire:click="cambiarEstatus('terminado')" class="justify-center w-full bg-red-600 hover:bg-red-700">3. Finalizar/Cerrar Ticket</x-primary-button>
                        @endif

                        @if($solicitud->estatus === 'terminado')
                            <div class="text-center py-2 text-green-600 font-bold border-2 border-green-600 rounded">✓ TRABAJO FINALIZADO</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
    <!-- Modal para Visualizar Archivos -->
    <div x-data="{ open: false, url: '', type: '' }" 
         x-show="open" 
         x-on:open-modal.window="open = true; url = $event.detail.url; type = $event.detail.type"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-90"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Vista Previa</h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-4 bg-gray-100 dark:bg-gray-900">
                    <template x-if="type === 'pdf'">
                        <iframe :src="url" class="w-full h-[70vh] border-0 rounded shadow-lg"></iframe>
                    </template>
                    <template x-if="type === 'image'">
                        <div class="flex justify-center">
                            <img :src="url" class="max-w-full max-h-[70vh] object-contain rounded shadow-lg">
                        </div>
                    </template>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 flex justify-end gap-2">
                    <a :href="url" target="_blank" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-md hover:bg-indigo-700 transition">Abrir en nueva pestaña</a>
                    <button @click="open = false" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-bold rounded-md hover:bg-gray-300 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
