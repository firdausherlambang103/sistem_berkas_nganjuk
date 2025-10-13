<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <main class="w-full sm:max-w-4xl mt-6 bg-white shadow-md overflow-hidden sm:rounded-lg flex flex-wrap">
                {{-- KOLOM KANAN: FORM LOGIN/REGISTER --}}
                <div class="w-full sm:w-1/2 p-6 sm:p-8">
                    <div class="flex justify-start mb-6">
                        <a href="/">
                            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                        </a>
                    </div>
                    {{ $slot }}
                </div>

                {{-- KOLOM KIRI: VISUAL/BRANDING --}}
                <div class="hidden sm:block sm:w-1/2 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1521791055366-0d553872125f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80');">
                    <div class="w-full h-full bg-indigo-700 bg-opacity-70 flex items-center justify-center p-8">
                        <div>
                            <h1 class="text-white text-3xl font-bold mb-3">Sistem Tracking Berkas</h1>
                            <p class="text-indigo-200">Selamat datang kembali! Silakan masuk untuk melanjutkan pekerjaan Anda dan memonitor pergerakan berkas secara efisien.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>