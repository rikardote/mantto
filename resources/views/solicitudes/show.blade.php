<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalles de la Solicitud') }} #{{ $solicitud->id }}
            </h2>
            <a href="{{ route('solicitudes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                &larr; Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:solicitudes.show :solicitud="$solicitud" />
        </div>
    </div>
</x-app-layout>
