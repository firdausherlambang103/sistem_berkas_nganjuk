<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Livewire Styles (Wajib dikembalikan untuk styling komponen) --}}
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>

            {{-- Komponen Chat Box (Floating) --}}
            @livewire('chat-box')
        </div>

        {{-- Komponen Modal Wire Elements (Wajib ada untuk fitur Popup/Modal Livewire) --}}
        @livewire('wire-elements-modal')

        {{-- Livewire Scripts (Wajib dikembalikan agar interaksi JS berjalan) --}}
        @livewireScripts

        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        @stack('scripts')
    </body>
</html>