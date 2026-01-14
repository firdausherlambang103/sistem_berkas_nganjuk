@php
    $masuk = $user->total_masuk;
    $keluar = $user->total_keluar;
    
    // PERBAIKAN: Hitung persen dan batasi maksimal 100%
    $hitungPersen = $masuk > 0 ? ($keluar / $masuk) * 100 : 0;
    $persen = round($hitungPersen > 100 ? 100 : $hitungPersen);
    
    // Warna Indikator (Gunakan 'green' & 'yellow' yang standar)
    $textWarna = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
    $bgClass = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
    
    $isKepala = $isKepala ?? false; 
    $tahunTampil = request('tahun', date('Y'));
@endphp

@if($isKepala)
    {{-- TAMPILAN KEPALA KANTOR (Horizontal Besar) --}}
    <div class="bg-white rounded-2xl shadow-lg border-l-8 border-yellow-400 p-4 w-96 relative overflow-hidden flex items-center gap-4 transform hover:scale-105 transition duration-300">
        {{-- Avatar --}}
        <div class="flex-shrink-0">
            <div class="h-20 w-20 rounded-full bg-yellow-100 flex items-center justify-center text-3xl font-bold text-yellow-700 border-4 border-white shadow-md">
                {{ substr($user->name, 0, 1) }}
            </div>
        </div>
        {{-- Info --}}
        <div class="flex-grow min-w-0">
            <h3 class="text-[10px] font-bold text-yellow-600 uppercase tracking-widest mb-1">
                <i class="fas fa-crown mr-1"></i> {{ $jabatan->nama_jabatan }}
            </h3>
            <h4 class="text-xl font-bold text-gray-800 leading-tight truncate mb-2">{{ $user->name }}</h4>
            
            {{-- Stats --}}
            <div class="flex justify-between bg-gray-50 rounded-lg p-2 border border-gray-100">
                <div class="text-center w-1/3">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Masuk</span>
                    <span class="block text-lg font-black text-blue-600 leading-none">{{ $masuk }}</span>
                </div>
                <div class="text-center w-1/3 border-l border-gray-200">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Selesai</span>
                    <span class="block text-lg font-black text-green-600 leading-none">{{ $keluar }}</span>
                </div>
                <div class="text-center w-1/3 border-l border-gray-200">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Sisa</span>
                    <span class="block text-lg font-black text-red-600 leading-none">{{ $user->sisa_berkas }}</span>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- TAMPILAN PEGAWAI BIASA (Horizontal Row Style) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 hover:shadow-md transition-all duration-200 flex items-center gap-3 relative overflow-hidden h-28 w-full group">
        
        {{-- Bar Progress Bawah --}}
        <div class="absolute bottom-0 left-0 h-1.5 {{ $bgClass }} transition-all duration-1000" style="width: {{ $persen }}%"></div>

        {{-- Kiri: Identitas --}}
        <div class="w-5/12 border-r border-gray-100 pr-3 flex flex-col justify-center h-full">
            <div class="flex items-center gap-3 mb-2">
                <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-lg border border-indigo-100 shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-gray-800 text-sm leading-snug line-clamp-2" title="{{ $user->name }}">
                        {{ $user->name }}
                    </h4>
                </div>
            </div>
            <p class="text-[10px] text-gray-400 truncate">NIP. {{ $user->nip ?? '-' }}</p>
            
            @if($user->produktivitas_harian > 0)
                <span class="mt-1 inline-flex items-center bg-green-50 text-green-700 text-[9px] font-bold px-2 py-0.5 rounded border border-green-100 w-fit">
                    <i class="fa-solid fa-bolt mr-1"></i> Hari Ini: {{ $user->produktivitas_harian }}
                </span>
            @endif
        </div>

        {{-- Tengah: Statistik Grid --}}
        <div class="w-5/12 px-1">
            <div class="grid grid-cols-3 gap-1 text-center">
                <div class="bg-blue-50/50 rounded py-1">
                    <span class="block text-[8px] text-blue-500 font-bold uppercase">Masuk</span>
                    <span class="block text-sm font-black text-gray-700">{{ $masuk }}</span>
                </div>
                <div class="bg-green-50/50 rounded py-1">
                    <span class="block text-[8px] text-green-500 font-bold uppercase">Selesai</span>
                    <span class="block text-sm font-black text-gray-700">{{ $keluar }}</span>
                </div>
                <div class="bg-red-50/50 rounded py-1">
                    <span class="block text-[8px] text-red-500 font-bold uppercase">Sisa</span>
                    <span class="block text-sm font-black text-gray-700">{{ $user->sisa_berkas }}</span>
                </div>
            </div>
        </div>

        {{-- Kanan: Persentase --}}
        <div class="w-2/12 flex flex-col items-center justify-center pl-2 border-l border-gray-100">
            <span class="text-[9px] text-gray-400 font-bold uppercase">Target</span>
            <span class="text-xl font-black {{ $textWarna }}">{{ $persen }}%</span>
        </div>

        {{-- Link Full Card --}}
        <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => $tahunTampil]) }}" 
           class="absolute inset-0 z-10 rounded-xl focus:outline-none"
           title="Detail {{ $user->name }}">
        </a>
    </div>
@endif