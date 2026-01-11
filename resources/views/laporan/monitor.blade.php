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
    <div class="bg-indigo-900 shadow-xl border-b border-indigo-700 py-3 px-6 flex justify-between items-center text-white h-20 shrink-0 z-50">
        <div class="flex items-center gap-4">
            <div class="bg-white/10 p-2 rounded backdrop-blur-sm border border-white/20">
                <i class="fas fa-chart-line text-yellow-400 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight leading-none text-white">MONITOR KINERJA</h1>
                <p class="text-xs text-indigo-200 font-bold mt-0.5 uppercase tracking-wider">
                    <i class="far fa-clock text-yellow-400 mr-1"></i> <span id="clock" class="text-white">--:--:--</span>
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('laporan.monitor') }}" class="flex gap-3">
             <select name="tahun" onchange="this.form.submit()" class="py-2 pl-4 pr-8 rounded-full border-2 border-indigo-500 bg-indigo-800 text-white font-bold text-sm focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer hover:bg-indigo-700 transition shadow-lg">
                @for($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}" class="bg-white text-gray-800" {{ (request('tahun') ?? date('Y')) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <select name="seksi" onchange="this.form.submit()" class="py-2 pl-4 pr-8 rounded-full border-2 border-indigo-500 bg-indigo-800 text-white font-bold text-sm focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer hover:bg-indigo-700 transition shadow-lg">
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
    {{-- id="scrollContainer" wajib ada untuk auto scroll JS --}}
    <div id="scrollContainer" class="flex-1 overflow-x-auto hide-scrollbar p-6 bg-gray-100">
        
        <div class="flex gap-8 w-max h-full items-start">
            
            {{-- LOOP JABATAN --}}
            @foreach($jabatans as $jabatan)
                @if($jabatan->users->isNotEmpty())
                    
                    {{-- 1. KEPALA KANTOR (Tampil Sendiri) --}}
                    @if($jabatan->nama_jabatan === 'Kepala Kantor Pertanahan')
                        <div class="flex flex-col h-full justify-center min-w-[24rem]">
                            @foreach($jabatan->users as $user)
                                @include('laporan.monitor-card', ['user' => $user, 'jabatan' => $jabatan, 'isKepala' => true])
                            @endforeach
                        </div>

                    {{-- 2. JABATAN LAIN (Grid 8 Baris) --}}
                    @else
                        <div class="flex flex-col h-full bg-white/50 border border-white/60 rounded-2xl shadow-sm backdrop-blur-sm overflow-hidden">
                            
                            {{-- Header Jabatan --}}
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-3 text-white flex justify-between items-center shadow-md shrink-0 z-10 w-[28rem] sticky left-0">
                                <h3 class="font-bold text-lg leading-tight truncate flex items-center gap-2">
                                    {{ $jabatan->nama_jabatan }}
                                </h3>
                                <span class="bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full border border-white/20">
                                    {{ $jabatan->users->count() }}
                                </span>
                            </div>

                            {{-- CONTENT GRID --}}
                            {{-- MENGGUNAKAN STYLE INLINE AGAR PASTI JALAN (8 BARIS) --}}
                            <div class="p-4 h-full">
                                <div class="grid grid-flow-col gap-4 content-start" 
                                     style="grid-template-rows: repeat(8, min-content);">
                                    @foreach($jabatan->users as $user)
                                        <div class="w-[26rem]"> {{-- Lebar fix agar grid rapi --}}
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
                document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID');
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 2. AUTO REFRESH (5 Menit)
            setTimeout(() => { 
                window.location.reload(); 
            }, 300000);

            // 3. AUTO SCROLL HORIZONTAL (DIPERBAIKI)
            const container = document.getElementById('scrollContainer');
            let scrollSpeed = 1; // Kecepatan scroll
            let direction = 1;   // 1 = kanan, -1 = kiri
            let isPaused = false;

            function autoScroll() {
                if (isPaused) return;

                // Hanya scroll jika konten lebih lebar dari layar
                if (container.scrollWidth > container.clientWidth) {
                    container.scrollLeft += (scrollSpeed * direction);

                    // Mentok Kanan -> Balik Kiri
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 1) {
                        isPaused = true;
                        setTimeout(() => { 
                            direction = -1; 
                            isPaused = false; 
                        }, 3000); // Tunggu 3 detik
                    }
                    // Mentok Kiri -> Balik Kanan
                    else if (container.scrollLeft <= 0) {
                        isPaused = true;
                        setTimeout(() => { 
                            direction = 1; 
                            isPaused = false; 
                        }, 3000); // Tunggu 3 detik
                    }
                }
            }

            // Interval halus
            setInterval(autoScroll, 20);
        });
    </script>
</body>
</html>