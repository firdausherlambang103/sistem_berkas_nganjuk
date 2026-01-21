<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Monitor Kinerja - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Sembunyikan scrollbar agar bersih */
        html, body { overflow: hidden; height: 100%; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 h-screen flex flex-col">

    {{-- HEADER (FIXED TOP) --}}
    <div class="bg-indigo-900 shadow-xl border-b border-indigo-700 py-3 px-8 flex justify-between items-center text-white h-24 shrink-0 z-50">
        <div class="flex items-center gap-5">
            <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20 shadow-inner">
                <i class="fas fa-chart-line text-yellow-400 text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black tracking-tight leading-none text-white drop-shadow-md">MONITOR KINERJA</h1>
                <p class="text-sm text-indigo-200 font-bold mt-1 uppercase tracking-widest">
                    <i class="far fa-clock text-yellow-400 mr-1.5"></i> <span id="clock" class="text-white">--:--:--</span>
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('laporan.monitor') }}" class="flex gap-4">
             <select name="tahun" onchange="this.form.submit()" class="py-2.5 pl-5 pr-10 rounded-full border-2 border-indigo-500 bg-indigo-800 text-white font-bold text-base focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer hover:bg-indigo-700 transition shadow-lg">
                @for($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}" class="bg-white text-gray-800" {{ (request('tahun') ?? date('Y')) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <select name="seksi" onchange="this.form.submit()" class="py-2.5 pl-5 pr-10 rounded-full border-2 border-indigo-500 bg-indigo-800 text-white font-bold text-base focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer hover:bg-indigo-700 transition shadow-lg">
                <option value="" class="text-gray-300">-- SEMUA SEKSI --</option>
                @foreach($listSeksi as $seksi)
                    <option value="{{ $seksi }}" class="bg-white text-gray-800" {{ isset($currentSeksi) && $currentSeksi == $seksi ? 'selected' : '' }}>
                        {{ strtoupper($seksi) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- MAIN CONTENT (Horizontal Scroll Container) --}}
    <div id="scrollContainer" class="flex-1 overflow-x-auto hide-scrollbar p-8 bg-gray-100">
        
        <div class="flex gap-10 w-max h-full items-start">
            
            {{-- LOOP JABATAN --}}
            @foreach($jabatans as $jabatan)
                @if($jabatan->users->isNotEmpty())
                    
                    {{-- 1. KEPALA KANTOR (Tampil Sendiri) --}}
                    @if($jabatan->nama_jabatan === 'Kepala Kantor Pertanahan')
                        <div class="flex flex-col h-full justify-center min-w-[32rem]">
                            @foreach($jabatan->users as $user)
                                @include('laporan.monitor-card', ['user' => $user, 'jabatan' => $jabatan, 'isKepala' => true])
                            @endforeach
                        </div>

                    {{-- 2. JABATAN LAIN --}}
                    @else
                        <div class="flex flex-col h-full bg-white/60 border border-white rounded-3xl shadow-sm backdrop-blur-md overflow-hidden">
                            
                            {{-- Header Jabatan --}}
                            <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 text-white flex justify-between items-center shadow-lg shrink-0 z-10 w-[30rem] sticky left-0">
                                <h3 class="font-bold text-xl leading-tight truncate flex items-center gap-2">
                                    {{ $jabatan->nama_jabatan }}
                                </h3>
                                <span class="bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full border border-white/20 shadow-sm">
                                    {{ $jabatan->users->count() }} Pegawai
                                </span>
                            </div>

                            {{-- CONTENT GRID --}}
                            {{-- Ubah menjadi 5 baris agar muat dengan kartu yang lebih besar (h-40) --}}
                            <div class="p-5 h-full">
                                <div class="grid grid-flow-col gap-5 content-start" 
                                     style="grid-template-rows: repeat(5, min-content);">
                                    @foreach($jabatan->users as $user)
                                        <div class="w-[28rem]"> {{-- Lebar container kartu --}}
                                            @include('laporan.monitor-card', ['user' => $user, 'jabatan' => $jabatan, 'isKepala' => false])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                @endif
            @endforeach

        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. JAM
            function updateClock() {
                const now = new Date();
                document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 2. AUTO REFRESH (5 Menit)
            setTimeout(() => { 
                window.location.reload(); 
            }, 300000);

            // 3. AUTO SCROLL HORIZONTAL
            const container = document.getElementById('scrollContainer');
            let scrollSpeed = 1; 
            let direction = 1;   
            let isPaused = false;

            function autoScroll() {
                if (isPaused) return;

                if (container.scrollWidth > container.clientWidth) {
                    container.scrollLeft += (scrollSpeed * direction);

                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 2) {
                        isPaused = true;
                        setTimeout(() => { 
                            direction = -1; 
                            isPaused = false; 
                        }, 4000); 
                    }
                    else if (container.scrollLeft <= 0) {
                        isPaused = true;
                        setTimeout(() => { 
                            direction = 1; 
                            isPaused = false; 
                        }, 4000);
                    }
                }
            }
            setInterval(autoScroll, 25);
        });
    </script>
</body>
</html>