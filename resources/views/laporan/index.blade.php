<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- FILTER SECTION --}}
            <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('laporan.index') }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        
                        {{-- Judul Filter --}}
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-100 p-3 rounded-xl text-indigo-600">
                                <i class="fa-solid fa-filter text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Filter Data</h3>
                                <p class="text-sm text-gray-500">Tampilkan kinerja berdasarkan parameter</p>
                            </div>
                        </div>

                        {{-- Area Input Filter --}}
                        <div class="flex flex-col sm:flex-row gap-4 flex-1 lg:justify-end">
                            
                            {{-- Filter Tahun --}}
                            <div class="w-full sm:w-auto">
                                <label for="tahun" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tahun</label>
                                <div class="relative">
                                    <i class="fa-regular fa-calendar-days absolute left-3 top-3 text-gray-400 pointer-events-none"></i>
                                    <select name="tahun" id="tahun" onchange="this.form.submit()" 
                                            class="pl-10 w-full sm:w-40 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 cursor-pointer bg-gray-50 hover:bg-white transition-colors font-semibold">
                                        @for($y = date('Y'); $y >= 2024; $y--)
                                            <option value="{{ $y }}" {{ (request('tahun') ?? date('Y')) == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            {{-- Filter Seksi --}}
                            <div class="w-full sm:w-72">
                                <label for="seksi" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Seksi / Jabatan</label>
                                <div class="relative">
                                    <i class="fa-solid fa-sitemap absolute left-3 top-3 text-gray-400 pointer-events-none"></i>
                                    <select name="seksi" id="seksi" onchange="this.form.submit()" 
                                            class="pl-10 w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 cursor-pointer bg-gray-50 hover:bg-white transition-colors font-semibold">
                                        <option value="">-- Semua Seksi --</option>
                                        @foreach($listSeksi as $seksi)
                                            <option value="{{ $seksi }}" {{ request('seksi') == $seksi ? 'selected' : '' }}>
                                                {{ $seksi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tombol Reset --}}
                            @if(request('seksi') || request('tahun') != date('Y'))
                                <div class="flex items-end pb-0.5">
                                    <a href="{{ route('laporan.index') }}" class="text-sm font-bold text-red-500 hover:text-red-700 hover:bg-red-50 px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 border border-transparent hover:border-red-200" title="Hapus Filter">
                                        <i class="fa-solid fa-rotate-left"></i> <span class="hidden sm:inline">Reset</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- DATA CONTENT --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 items-start">
                
                @foreach($jabatans as $jabatan)
                    @if($jabatan->users->isNotEmpty())
                    
                    @php
                        $jumlahPegawai = $jabatan->users->count();
                        $namaJabatan = $jabatan->nama_jabatan;
                        $isPejabat = \Illuminate\Support\Str::contains($namaJabatan, ['Kepala Kantor', 'Kepala Seksi']);

                        if ($isPejabat) {
                            $colSpanClass = 'xl:col-span-2'; 
                            $innerGridClass = 'flex flex-wrap justify-center gap-8'; // Gap lebih besar
                            $cardSizeClass = 'w-full max-w-3xl min-h-[280px]'; 
                            $paddingClass = 'p-8';
                            $avatarSize = 'h-16 w-16 text-2xl'; 
                            $nameSize = 'text-2xl'; 
                        } else {
                            $colSpanClass = $jumlahPegawai === 1 ? 'xl:col-span-1' : 'xl:col-span-2';
                            // Grid disesuaikan, jika banyak data pakai 3 kolom di layar besar
                            $innerGridClass = $jumlahPegawai === 1 ? 'grid gap-6 grid-cols-1' : 'grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
                            $cardSizeClass = 'h-full min-h-[240px]'; // Tinggi minimal ditambah
                            $paddingClass = 'p-6'; // Padding ditambah
                            $avatarSize = 'h-12 w-12 text-lg';
                            $nameSize = 'text-base';
                        }
                    @endphp

                    {{-- Container Jabatan --}}
                    <div class="w-full bg-white rounded-3xl p-6 border border-gray-100 shadow-sm {{ $colSpanClass }}">
                        
                        {{-- Header Jabatan --}}
                        <div class="flex items-center gap-4 mb-6 pb-3 border-b border-gray-100 {{ $isPejabat ? 'justify-center' : '' }}">
                            <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                            <h3 class="text-xl font-bold text-gray-800 tracking-tight">
                                {{ $jabatan->nama_jabatan }}
                            </h3>
                            @if(!$isPejabat) 
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full ml-auto border border-indigo-100">
                                    {{ $jumlahPegawai }} Org
                                </span>
                            @endif
                        </div>

                        {{-- Grid Pegawai --}}
                        <div class="{{ $innerGridClass }}">
                            @foreach($jabatan->users as $user)
                                @php
                                    $masuk = $user->total_masuk; 
                                    $keluar = $user->total_keluar;
                                    
                                    // Variabel baru (sesuai controller sebelumnya)
                                    $proses = $user->berkas_proses ?? 0;
                                    $pending = $user->berkas_pending ?? 0;
                                    $jatuhTempo = $user->berkas_jatuh_tempo ?? 0;

                                    $total_beban = $keluar + $proses + $pending;
                                    $persen = $total_beban > 0 ? round(($keluar / $total_beban) * 100) : 0;
                                    
                                    $warnaText = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                    $bgBar = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                    
                                    $harian = $user->produktivitas_harian;
                                @endphp

                                {{-- Card Pegawai --}}
                                <div class="{{ $cardSizeClass }} bg-white rounded-2xl shadow-sm hover:shadow-lg border border-gray-200 hover:border-indigo-300 transition-all duration-300 relative group flex flex-col overflow-hidden">
                                    
                                    {{-- Hover Effect Bar --}}
                                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                                    <div class="{{ $paddingClass }} flex-1 flex flex-col">
                                        
                                        {{-- Badge Harian --}}
                                        <div class="absolute top-4 right-4">
                                            @if($harian > 0)
                                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-lg border border-green-200 flex items-center gap-1.5 shadow-sm">
                                                    <i class="fa-solid fa-check-double"></i> +{{ $harian }}
                                                </span>
                                            @else
                                                <span class="bg-gray-100 text-gray-400 text-xs font-bold px-2.5 py-1 rounded-lg border border-gray-200">
                                                    0
                                                </span>
                                            @endif
                                        </div>

                                        {{-- User Info --}}
                                        <div class="flex items-center gap-4 mb-6 pr-16">
                                            <div class="{{ $avatarSize }} rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-600 flex items-center justify-center font-bold border border-indigo-100 shadow-inner flex-shrink-0">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-gray-900 {{ $nameSize }} truncate mb-0.5" title="{{ $user->name }}">
                                                    {{ $user->name }}
                                                </h4>
                                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide truncate">
                                                    NIP. {{ $user->nip ?? '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-100 mb-4"></div>

                                        {{-- Stats Grid (5 Kolom - Ukuran Font Diperbaiki) --}}
                                        <div class="grid grid-cols-5 gap-2 mb-6 text-center">
                                            {{-- Masuk --}}
                                            <div class="bg-blue-50 p-2 rounded-xl">
                                                <span class="block text-[10px] font-bold text-blue-500 uppercase tracking-tight truncate">Masuk</span>
                                                <span class="block text-lg font-black text-blue-700 leading-tight mt-1">{{ $masuk }}</span>
                                            </div>
                                            
                                            {{-- Proses --}}
                                            <div class="bg-yellow-50 p-2 rounded-xl border border-yellow-100">
                                                <span class="block text-[10px] font-bold text-yellow-600 uppercase tracking-tight truncate">Proses</span>
                                                <span class="block text-lg font-black text-yellow-700 leading-tight mt-1">{{ $proses }}</span>
                                            </div>

                                            {{-- Pending --}}
                                            <div class="bg-orange-50 p-2 rounded-xl border border-orange-100">
                                                <span class="block text-[10px] font-bold text-orange-500 uppercase tracking-tight truncate">Hold</span>
                                                <span class="block text-lg font-black text-orange-700 leading-tight mt-1">{{ $pending }}</span>
                                            </div>

                                            {{-- Jatuh Tempo --}}
                                            <div class="bg-red-50 p-2 rounded-xl border border-red-100">
                                                <span class="block text-[10px] font-bold text-red-500 uppercase tracking-tight truncate">Telat</span>
                                                <span class="block text-lg font-black text-red-700 leading-tight mt-1">{{ $jatuhTempo }}</span>
                                            </div>

                                            {{-- Selesai --}}
                                            <div class="bg-green-50 p-2 rounded-xl">
                                                <span class="block text-[10px] font-bold text-green-600 uppercase tracking-tight truncate">Done</span>
                                                <span class="block text-lg font-black text-green-700 leading-tight mt-1">{{ $keluar }}</span>
                                            </div>
                                        </div>

                                        {{-- Progress Bar --}}
                                        <div class="mt-auto">
                                            <div class="flex justify-between items-end mb-2">
                                                <span class="text-xs font-semibold text-gray-500">Penyelesaian Th. {{ request('tahun', date('Y')) }}</span>
                                                <span class="text-sm font-black {{ $warnaText }}">{{ $persen }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                                <div class="h-2.5 rounded-full {{ $bgBar }} transition-all duration-1000" style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                        
                                        {{-- Link Detail --}}
                                        <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => request('tahun', date('Y'))]) }}" 
                                           class="absolute inset-0 z-10 rounded-2xl" 
                                           title="Lihat Detail">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
                
            </div> {{-- Tutup Wrapper Grid Utama --}}

        </div>
    </div>
</x-app-layout>