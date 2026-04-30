<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-50 to-indigo-100 dark:from-gray-950 dark:to-gray-900">
            <div class="mb-8">
                <a href="/" wire:navigate class="flex flex-col items-center">
                    <x-application-logo class="w-32 h-auto drop-shadow-xl mb-4" />
                    <h1 class="text-2xl font-bold text-indigo-900 dark:text-indigo-400 tracking-tight uppercase">Sistema de Mantenimiento</h1>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-widest mt-1">Control y Gestión Centralizada</p>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-8 py-10 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-2xl border border-white/20 dark:border-gray-700/30 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 font-semibold tracking-wider">© {{ date('Y') }} ISSSTE - México</p>
            </div>
        </div>
    </body>
</html>
