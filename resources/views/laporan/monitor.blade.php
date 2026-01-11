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
        /* Sembunyikan scrollbar agar tampilan bersih di TV/Monitor */
        body::-webkit-scrollbar { display: none; }
        body { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Animasi Background agar tidak kaku */
        .animated-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 overflow-x-hidden min-h-screen">

    <div class="fixed top-0 left-0 right-0 z-50 bg-indigo-900 shadow-xl border-b border-indigo-700 py-3 px-6 flex justify-between items-center text-white">
        <div class="flex items-center gap-4">
            <div class="bg-white/10 p-2 rounded backdrop-blur-sm border border-white/20">
               <i class="fas fa-chart-line text-yellow-400 text-2xl"></i>
            </div>
            
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight leading-none text-white">
                    MONITOR KINERJA
                </h1>
                <p class="text-xs text-indigo-200 font-bold mt-0.5 uppercase tracking-wider">
                    <i class="far fa-clock text-yellow-400 mr-1"></i> 
                    <span id="clock" class="text-white">--:--:--</span>
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('laporan.monitor') }}">
            <select name="seksi" onchange="this.form.submit()" 
                    class="py-2 pl-4 pr-10 rounded-full border-2 border-indigo-500 bg-indigo-800 text-white font-bold text-sm focus:ring-yellow-400 focus:border-yellow-400 cursor-pointer hover:bg-indigo-700 transition shadow-lg">
                <option value="" class="text-gray-300">-- SEMUA SEKSI --</option>
                @foreach($listSeksi as $seksi)
                    <option value="{{ $seksi }}" class="bg-white text-gray-800" {{ isset($currentSeksi) && $currentSeksi == $seksi ? 'selected' : '' }}>
                        {{ strtoupper($seksi) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="pt-28 pb-12 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto w-full">
        
        @foreach($jabatans as $jabatan)
            @if($jabatan->nama_jabatan === 'Kepala Kantor Pertanahan' && $jabatan->users->isNotEmpty())
                <div class="mb-10 w-full">
                    <div class="bg-gradient-to-br from-yellow-50 to-white rounded-3xl shadow-xl border-t-8 border-yellow-500 p-8 text-center ring-1 ring-black/5">
                        
                        <h3 class="text-3xl font-black text-yellow-800 mb-8 uppercase tracking-widest inline-block border-b-4 border-yellow-200 pb-2">
                            <i class="fas fa-crown text-yellow-500 mr-2"></i> {{ $jabatan->nama_jabatan }}
                        </h3>

                        <div class="flex justify-center flex-wrap gap-8">
                            @foreach($jabatan->users as $user)
                                <div class="w-full md:w-1/3 lg:w-1/4 transform hover:scale-105 transition duration-300">
                                    @php
                                        // LOGIKA WARNA STANDARD (Green, Yellow, Red)
                                        $masuk = $user->total_masuk;
                                        $keluar = $user->total_keluar;
                                        $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
                                        
                                        $textWarna = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                        $bgBar = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                    @endphp
                                    
                                    <div class="bg-white rounded-2xl border-2 border-yellow-200 shadow-2xl overflow-hidden p-6 relative">
                                        <div class="absolute top-0 right-0 mt-4 mr-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                            Harian: {{ $user->produktivitas_harian }}
                                        </div>

                                        <div class="mx-auto h-20 w-20 rounded-full bg-yellow-100 flex items-center justify-center text-3xl font-bold text-yellow-700 border-4 border-white shadow-lg mb-4">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        
                                        <h4 class="text-xl font-bold text-gray-800 truncate">{{ $user->name }}</h4>
                                        <p class="text-sm text-gray-500 mb-4">{{ $user->id }}</p>

                                        <div class="grid grid-cols-3 gap-2 mb-4 bg-gray-50 p-2 rounded-xl">
                                            <div>
                                                <span class="text-xs font-bold text-gray-400 uppercase">Masuk</span>
                                                <div class="text-lg font-black text-blue-600">{{ $masuk }}</div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold text-gray-400 uppercase">Selesai</span>
                                                <div class="text-lg font-black text-green-600">{{ $keluar }}</div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold text-gray-400 uppercase">Sisa</span>
                                                <div class="text-lg font-black text-red-600">{{ $user->sisa_berkas }}</div>
                                            </div>
                                        </div>

                                        <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                                            <div class="h-4 rounded-full {{ $bgBar }} flex items-center justify-center text-[10px] text-white font-bold transition-all duration-1000" 
                                                 style="width: {{ $persen }}%">
                                                {{ $persen }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="columns-1 md:columns-2 lg:columns-3 xl:columns-4 gap-6 space-y-6">
            @foreach($jabatans as $jabatan)
                @if($jabatan->nama_jabatan !== 'Kepala Kantor Pertanahan' && $jabatan->users->isNotEmpty())
                    
                    <div class="break-inside-avoid bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-6 hover:shadow-2xl transition-all duration-300">
                        
                        <div class="bg-blue-600 px-4 py-3 flex justify-between items-center text-white">
                            <h3 class="font-bold text-lg leading-tight truncate pr-2" title="{{ $jabatan->nama_jabatan }}">
                                {{ $jabatan->nama_jabatan }}
                            </h3>
                            <span class="bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $jabatan->users->count() }}
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 flex flex-col gap-3">
                            @foreach($jabatan->users as $user)
                                @php
                                    // Hitung Statistik
                                    $masuk = $user->total_masuk;
                                    $keluar = $user->total_keluar;
                                    $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
                                    
                                    // Warna Standard (Pasti Muncul)
                                    $textWarna = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                    // Gunakan style width untuk progress bar agar aman
                                    $bgClass = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                @endphp

                                <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 {{ $persen >= 80 ? 'border-green-500' : ($persen >= 50 ? 'border-yellow-400' : 'border-red-500') }} relative group hover:bg-blue-50 transition-colors">
                                    
                                    <div class="absolute top-2 right-2 {{ $user->produktivitas_harian > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }} text-[10px] font-bold px-2 py-0.5 rounded border">
                                        Hari Ini: {{ $user->produktivitas_harian }}
                                    </div>

                                    <div class="flex items-center mb-2">
                                        <div class="h-8 w-8 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm mr-2 border border-indigo-200">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="font-bold text-gray-800 text-sm truncate w-32 md:w-40" title="{{ $user->name }}">
                                                {{ $user->name }}
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center bg-gray-50 rounded px-2 py-1 mb-2 border border-gray-100">
                                        <div class="text-center">
                                            <span class="block text-[9px] text-gray-400 uppercase">Masuk</span>
                                            <span class="block text-sm font-bold text-blue-600">{{ $masuk }}</span>
                                        </div>
                                        <div class="text-center border-l border-r border-gray-200 px-2">
                                            <span class="block text-[9px] text-gray-400 uppercase">Selesai</span>
                                            <span class="block text-sm font-bold text-green-600">{{ $keluar }}</span>
                                        </div>
                                        <div class="text-center">
                                            <span class="block text-[9px] text-gray-400 uppercase">Sisa</span>
                                            <span class="block text-sm font-bold text-red-600">{{ $user->sisa_berkas }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="flex-grow bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $bgClass }}" style="width: {{ $persen }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold {{ $textWarna }} w-8 text-right">{{ $persen }}%</span>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. JAM
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                document.getElementById('clock').textContent = timeString;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 2. AUTO REFRESH (5 Menit)
            setTimeout(() => {
                window.location.reload();
            }, 60000); 

            // 3. AUTO SCROLL (Looping)
            let scrollSpeed = 1; // Kecepatan scroll
            let isScrolling = true;
            
            function autoScroll() {
                if (!isScrolling) return;

                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 2) {
                    // Stop sebentar di bawah
                    isScrolling = false;
                    setTimeout(() => {
                        // Kembali ke atas pelan (smooth)
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        // Tunggu sampai sampai atas, baru scroll lagi
                        setTimeout(() => { isScrolling = true; }, 3000);
                    }, 3000);
                } else {
                    window.scrollBy(0, scrollSpeed);
                }
            }
            setInterval(autoScroll, 50); // Interval scroll
        });
    </script>
</body>
</html>