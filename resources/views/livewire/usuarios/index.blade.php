<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Unidad;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $name = '';
    public $email = '';
    public $password = '';
    public $rol = 'unidad';
    public $unidad_id = '';
    
    public $editingUser = null;
    public $isModalOpen = false;

    public function with()
    {
        return [
            'usuarios' => User::with('unidad')->orderBy('name')->get(),
            'unidades' => Unidad::all(),
        ];
    }

    public function openModal($userId = null)
    {
        $this->reset(['name', 'email', 'password', 'rol', 'unidad_id', 'editingUser']);
        
        if ($userId) {
            $this->editingUser = User::find($userId);
            $this->name = $this->editingUser->name;
            $this->email = $this->editingUser->email;
            $this->rol = $this->editingUser->rol;
            $this->unidad_id = $this->editingUser->unidad_id;
        }

        $this->isModalOpen = true;
    }

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'rol' => ['required', 'in:unidad,supervisor,tecnico'],
            'unidad_id' => ['required_if:rol,unidad'],
        ];

        if ($this->editingUser) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$this->editingUser->id];
            if ($this->password) {
                $rules['password'] = ['string', Rules\Password::defaults()];
            }
        } else {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'];
            $rules['password'] = ['required', Rules\Password::defaults()];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'unidad_id' => $this->rol === 'unidad' ? $this->unidad_id : null,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            session()->flash('status', 'Usuario actualizado correctamente.');
        } else {
            User::create($data);
            session()->flash('status', 'Usuario creado correctamente.');
        }

        $this->isModalOpen = false;
    }

    public function deleteUser($userId)
    {
        if ($userId == Auth::id()) {
            return;
        }
        User::destroy($userId);
        session()->flash('status', 'Usuario eliminado.');
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Gestión de Usuarios</h3>
        <x-primary-button wire:click="openModal()">Nuevo Usuario</x-primary-button>
    </div>

    @if (session('status'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($usuarios as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $user->rol === 'supervisor' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ strtoupper($user->rol) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $user->unidad->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">Editar</button>
                                @if($user->id != Auth::id())
                                    <button wire:click="deleteUser({{ $user->id }})" onclick="return confirm('¿Seguro?')" class="text-red-600 hover:text-red-900">Eliminar</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-md">
                <h2 class="text-xl font-bold mb-4 dark:text-white">{{ $editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
                
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre Completo" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required />
                    </div>

                    <div>
                        <x-input-label for="password" value="{{ $editingUser ? 'Nueva Contraseña (vacío para no cambiar)' : 'Contraseña' }}" />
                        <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" :required="!$editingUser" />
                    </div>

                    <div>
                        <x-input-label for="rol" value="Rol" />
                        <select wire:model.live="rol" id="rol" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="unidad">Unidad (Centro de Trabajo)</option>
                            <option value="supervisor">Supervisor (Administrador)</option>
                        </select>
                    </div>

                    @if($rol === 'unidad')
                        <div>
                            <x-input-label for="unidad_id" value="Unidad Asignada" />
                            <select wire:model="unidad_id" id="unidad_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                <option value="">Seleccione una unidad...</option>
                                @foreach($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('isModalOpen', false)" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancelar</button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
