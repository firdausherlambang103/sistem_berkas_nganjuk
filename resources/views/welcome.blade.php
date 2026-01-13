<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Pelacakan Berkas Digital & SIMBUTA</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 text-gray-800 selection:bg-indigo-500 selection:text-white">
        
        <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">
            
            {{-- Background Decoration --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
                <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-indigo-200/20 blur-3xl"></div>
                <div class="absolute top-[20%] -right-[10%] w-[60%] h-[60%] rounded-full bg-blue-200/20 blur-3xl"></div>
                <div class="absolute -bottom-[20%] left-[20%] w-[50%] h-[50%] rounded-full bg-purple-200/20 blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 w-full">
                
                {{-- Top Navigation (Login/Register) --}}
                <div class="absolute top-6 right-6 z-20">
                    @if (Route::has('login'))
                        <div class="flex gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-indigo-600 transition">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-indigo-600 transition">Masuk</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="font-semibold text-gray-600 hover:text-indigo-600 transition">Daftar</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>

                {{-- HERO SECTION --}}
                <div class="text-center relative z-10">
                    
                    {{-- Logo / Icon --}}
                    <div class="flex justify-center mb-8 animate-fade-in-down">
                        <div class="p-5 bg-white rounded-3xl shadow-xl border border-white/50 ring-4 ring-indigo-50">
                            {{-- Icon Buku (Mewakili Simbuta) & Folder (Mewakili Berkas Digital) --}}
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-folder-tree text-5xl text-blue-500"></i>
                                <span class="text-gray-300 text-4xl">|</span>
                                <i class="fa-solid fa-book-journal-whills text-5xl text-indigo-600"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Title (DIGABUNGKAN) --}}
                    <h1 class="text-4xl md:text-6xl font-black mb-4 tracking-tight text-gray-900 leading-tight">
                        Sistem Pelacakan Berkas Digital <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">& SIMBUTA</span>
                    </h1>

                    {{-- Subtitle --}}
                    <h2 class="text-xl md:text-2xl font-bold text-gray-500 mb-6">
                        (Sistem Management Buku Tanah)
                    </h2>

                    {{-- Description --}}
                    <p class="text-lg md:text-xl text-gray-500 max-w-3xl mx-auto mb-10 leading-relaxed">
                        "Sinergi pengelolaan arsip modern. Memadukan kecepatan pelacakan berkas digital dengan keamanan manajemen buku tanah. 
                        Solusi terpadu untuk pelayanan pertanahan yang <span class="font-bold text-gray-700">Cepat</span>, <span class="font-bold text-gray-700">Aman</span>, dan <span class="font-bold text-gray-700">Transparan</span>."
                    </p>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="group relative px-8 py-4 bg-indigo-600 rounded-full text-white font-bold text-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <span class="relative z-10 flex items-center gap-2">
                                    <i class="fa-solid fa-gauge-high"></i> Buka Dashboard
                                </span>
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="group relative px-8 py-4 bg-indigo-600 rounded-full text-white font-bold text-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden w-full sm:w-auto">
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-right-to-bracket"></i> Login Petugas
                                </span>
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </a>
                        @endauth
                    </div>

                    {{-- Features Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                        {{-- Feature 1 --}}
                        <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-magnifying-glass-location text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-xl text-gray-900 mb-3">Pelacakan Berkas</h3>
                            <p class="text-gray-500 leading-relaxed">
                                Pantau posisi berkas permohonan secara real-time dari loket hingga penyerahan produk.
                            </p>
                        </div>

                        {{-- Feature 2 --}}
                        <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-6 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-book-bookmark text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-xl text-gray-900 mb-3">Manajemen Buku Tanah</h3>
                            <p class="text-gray-500 leading-relaxed">
                                (SIMBUTA) Pengelolaan peminjaman dan pengembalian buku tanah yang terstruktur dan aman.
                            </p>
                        </div>

                        {{-- Feature 3 --}}
                        <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mb-6 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-chart-pie text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-xl text-gray-900 mb-3">Monitoring Terpadu</h3>
                            <p class="text-gray-500 leading-relaxed">
                                Dashboard statistik komprehensif untuk memantau kinerja layanan berkas dan arsip buku tanah.
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="mt-20 text-center border-t border-gray-200 pt-8">
                    <p class="text-sm text-gray-400">
                        &copy; {{ date('Y') }} Kantor Pertanahan. <strong>Sistem Pelacakan Berkas & SIMBUTA</strong>.
                    </p>
                </div>

            </div>
        </div>
    </body>
</html>