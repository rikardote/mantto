<?php

use Livewire\Volt\Component;
use App\Models\Unidad;
use App\Models\Servicio;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $activeTab = 'unidades';
    
    // Form fields
    public $nombre = '';
    public $editingId = null;
    public $isModalOpen = false;

    public function with()
    {
        return [
            'unidades' => Unidad::orderBy('nombre')->get(),
            'servicios' => Servicio::orderBy('nombre')->get(),
        ];
    }

    public function openModal($id = null)
    {
        $this->reset(['nombre', 'editingId']);
        
        if ($id) {
            $this->editingId = $id;
            if ($this->activeTab === 'unidades') {
                $this->nombre = Unidad::find($id)->nombre;
            } else {
                $this->nombre = Servicio::find($id)->nombre;
            }
        }

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate(['nombre' => 'required|string|max:255']);

        if ($this->activeTab === 'unidades') {
            Unidad::updateOrCreate(['id' => $this->editingId], ['nombre' => $this->nombre]);
        } else {
            Servicio::updateOrCreate(['id' => $this->editingId], ['nombre' => $this->nombre]);
        }

        $this->isModalOpen = false;
        session()->flash('status', 'Registro guardado correctamente.');
    }

    public function delete($id)
    {
        try {
            if ($this->activeTab === 'unidades') {
                Unidad::destroy($id);
            } else {
                Servicio::destroy($id);
            }
            session()->flash('status', 'Registro eliminado.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar porque tiene registros asociados.');
        }
    }
}; ?>

<div class="space-y-6">
    <div class="flex border-b dark:border-gray-700">
        <button wire:click="$set('activeTab', 'unidades')" class="px-4 py-2 {{ $activeTab === 'unidades' ? 'border-b-2 border-indigo-500 text-indigo-600 font-bold' : 'text-gray-500' }}">
            Unidades / Áreas
        </button>
        <button wire:click="$set('activeTab', 'servicios')" class="px-4 py-2 {{ $activeTab === 'servicios' ? 'border-b-2 border-indigo-500 text-indigo-600 font-bold' : 'text-gray-500' }}">
            Tipos de Servicio
        </button>
    </div>

    <div class="flex justify-between items-center px-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Gestionar {{ $activeTab === 'unidades' ? 'Unidades' : 'Servicios' }}
        </h3>
        <x-primary-button wire:click="openModal()">+ Agregar {{ $activeTab === 'unidades' ? 'Unidad' : 'Servicio' }}</x-primary-button>
    </div>

    @if (session('status'))
        <div class="mx-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mx-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden mx-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @php $items = $activeTab === 'unidades' ? $unidades : $servicios; @endphp
                @forelse($items as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $item->nombre }}</td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="openModal({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">Editar</button>
                            <button wire:click="delete({{ $item->id }})" onclick="return confirm('¿Seguro?')" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-gray-500 italic">No hay registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md">
                <h2 class="text-xl font-bold mb-4 dark:text-white">{{ $editingId ? 'Editar' : 'Nuevo' }} {{ $activeTab === 'unidades' ? 'Unidad' : 'Servicio' }}</h2>
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <x-input-label for="nombre" value="Nombre" />
                        <x-text-input wire:model="nombre" id="nombre" class="block mt-1 w-full" type="text" required autofocus />
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('isModalOpen', false)" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancelar</button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
