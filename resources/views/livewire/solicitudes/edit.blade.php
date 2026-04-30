<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use App\Models\Servicio;
use App\Models\TipoMantenimiento;
use App\Models\Prioridad;

new class extends Component {
    public SolicitudMantenimiento $solicitud;
    public $titulo;
    public $descripcion;
    public $servicio_id;
    public $tipo_mantenimiento_id;
    public $prioridad_id;
    public $descripcion_servicio_otro;
    public $folio_oficio;

    public function mount(SolicitudMantenimiento $solicitud)
    {
        $this->authorize('update', $solicitud);
        $this->solicitud = $solicitud;
        $this->titulo = $solicitud->titulo;
        $this->descripcion = $solicitud->descripcion;
        $this->servicio_id = $solicitud->servicio_id;
        $this->tipo_mantenimiento_id = $solicitud->tipo_mantenimiento_id;
        $this->prioridad_id = $solicitud->prioridad_id;
        $this->descripcion_servicio_otro = $solicitud->descripcion_servicio_otro;
        $this->folio_oficio = $solicitud->folio_oficio;
    }

    public function save()
    {
        $this->authorize('update', $this->solicitud);
        
        $this->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'servicio_id' => 'required|exists:servicios,id',
            'tipo_mantenimiento_id' => 'required|exists:tipos_mantenimiento,id',
            'prioridad_id' => 'required|exists:prioridades,id',
        ]);

        $this->solicitud->update([
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'servicio_id' => $this->servicio_id,
            'tipo_mantenimiento_id' => $this->tipo_mantenimiento_id,
            'prioridad_id' => $this->prioridad_id,
            'descripcion_servicio_otro' => $this->descripcion_servicio_otro,
            'folio_oficio' => $this->folio_oficio,
        ]);

        return redirect()->route('solicitudes.show', $this->solicitud)
            ->with('status', 'Solicitud actualizada.');
    }

    public function with()
    {
        return [
            'servicios' => Servicio::all(),
            'tipos' => TipoMantenimiento::all(),
            'prioridades' => Prioridad::all(),
        ];
    }
}; ?>

<form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <x-input-label for="titulo" :value="__('Título')" />
            <x-text-input wire:model="titulo" class="block mt-1 w-full" type="text" required />
        </div>

        <div>
            <x-input-label for="servicio_id" :value="__('Servicio')" />
            <select wire:model="servicio_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                @foreach($servicios as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="folio_oficio" :value="__('Folio')" />
            <x-text-input wire:model="folio_oficio" class="block mt-1 w-full" type="text" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="descripcion" :value="__('Descripción')" />
            <textarea wire:model="descripcion" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
        </div>
    </div>

    <div class="flex items-center justify-end mt-4">
        <x-primary-button>
            {{ __('Guardar Cambios') }}
        </x-primary-button>
    </div>
</form>
