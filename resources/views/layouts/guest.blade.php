<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIMBUTA') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-white">
        
        <div class="min-h-screen flex w-full">
            
            {{-- KOLOM KIRI: VISUAL & BRANDING (Hidden on mobile) --}}
            <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 relative overflow-hidden items-center justify-center text-white p-12">
                
                {{-- Abstract Shapes --}}
                <div class="absolute top-0 left-0 w-full h-full opacity-20">
                    <div class="absolute top-10 left-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
                    <div class="absolute top-10 right-10 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
                    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
                </div>

                <div class="relative z-10 max-w-lg text-center">
                    {{-- Logo Besar --}}
                    <div class="flex justify-center mb-8">
                        <div class="p-6 bg-white/10 backdrop-blur-lg rounded-3xl border border-white/20 shadow-2xl">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-folder-tree text-5xl text-blue-300"></i>
                                <span class="text-white/50 text-4xl">|</span>
                                <i class="fa-solid fa-book-journal-whills text-5xl text-indigo-300"></i>
                            </div>
                        </div>
                    </div>

                    <h1 class="text-4xl font-extrabold mb-4 tracking-tight">Sistem Pelacakan Berkas & SIMBUTA</h1>
                    <p class="text-indigo-200 text-lg leading-relaxed">
                        Kelola arsip pertanahan dan lacak berkas digital dalam satu platform yang terintegrasi, aman, dan efisien.
                    </p>

                    <div class="mt-10 flex gap-4 justify-center">
                        <div class="flex items-center gap-2 text-sm font-medium bg-white/10 px-4 py-2 rounded-full border border-white/10">
                            <i class="fa-solid fa-check-circle text-green-400"></i> Realtime Tracking
                        </div>
                        <div class="flex items-center gap-2 text-sm font-medium bg-white/10 px-4 py-2 rounded-full border border-white/10">
                            <i class="fa-solid fa-shield-halved text-blue-400"></i> Data Secure
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORM --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 relative">
                
                {{-- Tombol Kembali --}}
                <div class="absolute top-6 right-6">
                    <a href="/" class="text-sm text-gray-500 hover:text-indigo-600 transition flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>

                <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                    {{-- Logo Mobile (Only visible on small screens) --}}
                    <div class="flex lg:hidden justify-center mb-8">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-folder-tree text-3xl text-indigo-600"></i>
                            <i class="fa-solid fa-book-journal-whills text-3xl text-indigo-600"></i>
                        </div>
                    </div>

                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>