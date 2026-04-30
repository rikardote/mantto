<?php

use Livewire\Volt\Component;
use App\Models\SolicitudMantenimiento;
use App\Models\Servicio;
use App\Models\TipoMantenimiento;
use App\Models\Prioridad;
use App\Models\User;
use App\Notifications\SolicitudActualizada;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $titulo = '';
    public $descripcion = '';
    public $unidad_id = '';
    public $servicio_id = '';
    public $tipo_mantenimiento_id = '';
    public $prioridad_id = '';
    public $descripcion_servicio_otro = '';
    public $folio_oficio = '';
    public $archivo_oficio;

    public function mount()
    {
        // Default priority to Media if available
        $media = Prioridad::where('nombre', 'Media')->first();
        if ($media) $this->prioridad_id = $media->id;
    }

    public function updatedTipoMantenimientoId($value)
    {
        $tipo = TipoMantenimiento::find($value);
        if ($tipo && $tipo->nombre === 'Emergencia') {
            $alta = Prioridad::where('nombre', 'Alta')->first();
            if ($alta) $this->prioridad_id = $alta->id;
        }
    }

    public function save()
    {
        $this->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'unidad_id' => Auth::user()->rol === 'supervisor' ? 'required|exists:unidades,id' : 'nullable',
            'servicio_id' => 'required|exists:servicios,id',
            'tipo_mantenimiento_id' => 'required|exists:tipos_mantenimiento,id',
            'prioridad_id' => 'required|exists:prioridades,id',
            'descripcion_servicio_otro' => 'required_if:servicio_id,' . $this->getOtroServicioId(),
            'archivo_oficio' => 'nullable|file|max:10240', // 10MB
        ]);

        $prioridad = Prioridad::find($this->prioridad_id);
        $fecha_solicitud = now();
        $fecha_limite = $fecha_solicitud->copy()->addHours($prioridad->tiempo_respuesta_horas ?? 48);

        $path = null;
        if ($this->archivo_oficio) {
            $path = $this->archivo_oficio->store('oficios', 'public');
        }

        $solicitud = SolicitudMantenimiento::create([
            'unidad_id' => $this->unidad_id ?: Auth::user()->unidad_id,
            'servicio_id' => $this->servicio_id,
            'tipo_mantenimiento_id' => $this->tipo_mantenimiento_id,
            'prioridad_id' => $this->prioridad_id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'descripcion_servicio_otro' => $this->descripcion_servicio_otro,
            'folio_oficio' => $this->folio_oficio,
            'archivo_oficio_path' => $path,
            'estatus' => 'abierto',
            'fecha_solicitud' => $fecha_solicitud,
            'fecha_limite' => $fecha_limite,
            'creado_por' => Auth::id(),
        ]);

        // Notificar a los usuarios de la Unidad destino
        $usuariosUnidad = User::where('unidad_id', $solicitud->unidad_id)->get();
        foreach ($usuariosUnidad as $user) {
            if ($user->id !== Auth::id()) { // No notificarse a uno mismo
                $user->notify(new SolicitudActualizada($solicitud, "Se ha registrado una nueva solicitud para tu unidad: " . $solicitud->titulo));
            }
        }

        return redirect()->route('solicitudes.index')
            ->with('status', 'Solicitud creada con éxito.');
    }

    private function getOtroServicioId()
    {
        return Servicio::where('nombre', 'Otro')->first()?->id;
    }

    public function with()
    {
        return [
            'servicios' => Servicio::where('activo', true)->get(),
            'tipos' => TipoMantenimiento::where('activo', true)->get(),
            'prioridades' => Prioridad::all(),
            'otro_id' => $this->getOtroServicioId(),
        ];
    }
}; ?>

<form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if(Auth::user()->rol === 'supervisor')
            <div class="md:col-span-2">
                <x-input-label for="unidad_id" :value="__('Unidad Solicitante')" />
                <select wire:model="unidad_id" id="unidad_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                    <option value="">Seleccione una unidad...</option>
                    @foreach(App\Models\Unidad::all() as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('unidad_id')" class="mt-2" />
            </div>
        @endif

        <!-- Titulo -->
        <div class="md:col-span-2">
            <x-input-label for="titulo" :value="__('Título de la Solicitud')" />
            <x-text-input wire:model="titulo" id="titulo" class="block mt-1 w-full" type="text" required autofocus placeholder="Ej: Falla en aire acondicionado pasillo B" />
            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
        </div>

        <!-- Servicio -->
        <div>
            <x-input-label for="servicio_id" :value="__('Área de Servicio')" />
            <select wire:model.live="servicio_id" id="servicio_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Seleccione un área...</option>
                @foreach($servicios as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('servicio_id')" class="mt-2" />
        </div>

        <!-- Folio Oficio (Opcional) -->
        <div>
            <x-input-label for="folio_oficio" :value="__('Folio de Oficio (Opcional)')" />
            <x-text-input wire:model="folio_oficio" id="folio_oficio" class="block mt-1 w-full" type="text" placeholder="Ej: OF-2024-001" />
            <x-input-error :messages="$errors->get('folio_oficio')" class="mt-2" />
        </div>

        <!-- Mostrar si es "Otro" -->
        @if($servicio_id == $otro_id && $otro_id)
            <div class="md:col-span-2">
                <x-input-label for="descripcion_servicio_otro" :value="__('Especifique el Servicio')" />
                <x-text-input wire:model="descripcion_servicio_otro" id="descripcion_servicio_otro" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('descripcion_servicio_otro')" class="mt-2" />
            </div>
        @endif

        <!-- Tipo Mantenimiento -->
        <div>
            <x-input-label for="tipo_mantenimiento_id" :value="__('Tipo de Mantenimiento')" />
            <select wire:model.live="tipo_mantenimiento_id" id="tipo_mantenimiento_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Seleccione tipo...</option>
                @foreach($tipos as $t)
                    <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('tipo_mantenimiento_id')" class="mt-2" />
        </div>

        <!-- Prioridad -->
        <div>
            <x-input-label for="prioridad_id" :value="__('Prioridad')" />
            <select wire:model="prioridad_id" id="prioridad_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">Seleccione prioridad...</option>
                @foreach($prioridades as $p)
                    <option value="{{ $p->id }}">{{ $p->nombre }} (SLA: {{ $p->tiempo_respuesta_horas }} hrs)</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('prioridad_id')" class="mt-2" />
        </div>

        <!-- Descripcion -->
        <div class="md:col-span-2">
            <x-input-label for="descripcion" :value="__('Descripción detallada del problema')" />
            <textarea wire:model="descripcion" id="descripcion" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
        </div>

        <!-- Archivo Oficio -->
        <div class="md:col-span-2">
            <x-input-label for="archivo_oficio" value="Adjuntar Oficio / Evidencia Inicial (PDF/Foto)" />
            <input type="file" wire:model="archivo_oficio" id="archivo_oficio" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mt-1">
            <div wire:loading wire:target="archivo_oficio" class="text-xs text-indigo-500 italic">Subiendo archivo...</div>
            <x-input-error :messages="$errors->get('archivo_oficio')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center justify-end mt-4">
        <x-secondary-button onclick="history.back()" class="mr-3">
            {{ __('Cancelar') }}
        </x-secondary-button>
        <x-primary-button>
            {{ __('Crear Solicitud') }}
        </x-primary-button>
    </div>
</form>
