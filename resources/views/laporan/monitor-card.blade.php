@php
    $masuk = $user->total_masuk;
    $keluar = $user->total_keluar;
    
    // Hitung persentase (Max 100%)
    $hitungPersen = $masuk > 0 ? ($keluar / $masuk) * 100 : 0;
    $persen = round($hitungPersen > 100 ? 100 : $hitungPersen);
    
    // Warna Indikator
    $textWarna = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
    $bgClass = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
    
    $isKepala = $isKepala ?? false; 
    $tahunTampil = request('tahun', date('Y'));
@endphp

@if($isKepala)
    {{-- TAMPILAN KEPALA KANTOR (Sangat Besar) --}}
    <div class="bg-white rounded-3xl shadow-xl border-l-[10px] border-yellow-400 p-6 w-[30rem] relative overflow-hidden flex items-center gap-6 transform hover:scale-105 transition duration-300">
        {{-- Avatar --}}
        <div class="flex-shrink-0">
            <div class="h-24 w-24 rounded-full bg-yellow-50 flex items-center justify-center text-4xl font-bold text-yellow-600 border-4 border-white shadow-md">
                {{ substr($user->name, 0, 1) }}
            </div>
        </div>
        {{-- Info --}}
        <div class="flex-grow min-w-0">
            <h3 class="text-xs font-bold text-yellow-600 uppercase tracking-widest mb-1">
                <i class="fas fa-crown mr-1"></i> {{ $jabatan->nama_jabatan }}
            </h3>
            <h4 class="text-2xl font-extrabold text-gray-800 leading-tight truncate mb-4">{{ $user->name }}</h4>
            
            {{-- Stats --}}
            <div class="flex justify-between bg-gray-50 rounded-xl p-3 border border-gray-100">
                <div class="text-center w-1/3">
                    <span class="block text-xs text-gray-500 font-bold uppercase tracking-wide">Masuk</span>
                    <span class="block text-2xl font-black text-blue-600 leading-none mt-1">{{ $masuk }}</span>
                </div>
                <div class="text-center w-1/3 border-l border-gray-200">
                    <span class="block text-xs text-gray-500 font-bold uppercase tracking-wide">Selesai</span>
                    <span class="block text-2xl font-black text-green-600 leading-none mt-1">{{ $keluar }}</span>
                </div>
                <div class="text-center w-1/3 border-l border-gray-200">
                    <span class="block text-xs text-gray-500 font-bold uppercase tracking-wide">Sisa</span>
                    <span class="block text-2xl font-black text-red-600 leading-none mt-1">{{ $user->sisa_berkas }}</span>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- TAMPILAN PEGAWAI BIASA (Lebih Lega) --}}
    {{-- Ubah tinggi h-28 menjadi h-40 agar info lebih jelas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 hover:shadow-lg transition-all duration-200 flex items-center gap-4 relative overflow-hidden h-40 w-full group">
        
        {{-- Bar Progress Bawah --}}
        <div class="absolute bottom-0 left-0 h-2 {{ $bgClass }} transition-all duration-1000" style="width: {{ $persen }}%"></div>

        {{-- Kiri: Identitas --}}
        <div class="w-5/12 border-r border-gray-100 pr-4 flex flex-col justify-center h-full">
            <div class="flex items-center gap-3 mb-3">
                <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xl border border-indigo-100 shrink-0 shadow-sm">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-gray-800 text-base leading-tight line-clamp-2" title="{{ $user->name }}">
                        {{ $user->name }}
                    </h4>
                </div>
            </div>
            
            {{-- NIP & Harian --}}
            <div class="flex flex-col gap-1">
                <p class="text-xs text-gray-400 font-mono truncate">
                    <i class="fa-regular fa-id-badge mr-1"></i>{{ $user->nip ?? '-' }}
                </p>
                @if($user->produktivitas_harian > 0)
                    <span class="inline-flex items-center bg-green-50 text-green-700 text-xs font-bold px-2 py-1 rounded-md border border-green-100 w-fit mt-1">
                        <i class="fa-solid fa-bolt mr-1.5 text-yellow-500"></i> Hari Ini: {{ $user->produktivitas_harian }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Tengah: Statistik Grid --}}
        <div class="w-5/12 px-2">
            <div class="grid grid-cols-1 gap-2">
                {{-- Row 1: Masuk & Selesai --}}
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50/60 rounded-lg p-2 text-center">
                        <span class="block text-[10px] text-blue-600 font-bold uppercase tracking-wider">Masuk</span>
                        <span class="block text-lg font-black text-blue-800 leading-none mt-0.5">{{ $masuk }}</span>
                    </div>
                    <div class="bg-green-50/60 rounded-lg p-2 text-center">
                        <span class="block text-[10px] text-green-600 font-bold uppercase tracking-wider">Selesai</span>
                        <span class="block text-lg font-black text-green-800 leading-none mt-0.5">{{ $keluar }}</span>
                    </div>
                </div>
                {{-- Row 2: Sisa (Full Width) --}}
                <div class="bg-red-50/60 rounded-lg p-1.5 text-center flex items-center justify-between px-4">
                    <span class="text-[10px] text-red-600 font-bold uppercase tracking-wider">Sisa Berkas</span>
                    <span class="text-lg font-black text-red-800 leading-none">{{ $user->sisa_berkas }}</span>
                </div>
            </div>
        </div>

        {{-- Kanan: Persentase --}}
        <div class="w-2/12 flex flex-col items-center justify-center pl-2 border-l border-gray-100 h-full">
            <div class="relative flex items-center justify-center">
                {{-- Circular Progress Placeholder (Visual Simpel) --}}
                <svg class="transform -rotate-90 w-14 h-14">
                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-100" />
                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="4" fill="transparent" :stroke-dasharray="150" :stroke-dashoffset="150 - (150 * {{ $persen }} / 100)" class="{{ $textWarna }}" />
                </svg>
                <span class="absolute text-sm font-black {{ $textWarna }}">{{ $persen }}%</span>
            </div>
            <span class="text-[10px] text-gray-400 font-bold uppercase mt-1">Kinerja</span>
        </div>

        {{-- Link Full Card --}}
        <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => $tahunTampil]) }}" 
           class="absolute inset-0 z-10 rounded-2xl focus:outline-none"
           title="Detail {{ $user->name }}">
        </a>
    </div>
@endif