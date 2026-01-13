<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- FILTER SECTION --}}
            <div class="mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('laporan.index') }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        
                        {{-- Judul Filter --}}
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Filter Data</h3>
                                <p class="text-xs text-gray-500">Tampilkan data spesifik</p>
                            </div>
                        </div>

                        {{-- Area Input Filter --}}
                        <div class="flex flex-col sm:flex-row gap-4 flex-1 lg:justify-end">
                            
                            {{-- Filter Tahun --}}
                            <div class="w-full sm:w-auto">
                                <label for="tahun" class="block text-xs font-semibold text-gray-500 mb-1 ml-1">Tahun Anggaran</label>
                                <div class="relative">
                                    <i class="fa-regular fa-calendar-days absolute left-3 top-2.5 text-gray-400 pointer-events-none"></i>
                                    <select name="tahun" id="tahun" onchange="this.form.submit()" 
                                            class="pl-9 w-full sm:w-36 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm cursor-pointer bg-gray-50 hover:bg-white transition-colors">
                                        @for($y = date('Y'); $y >= 2024; $y--)
                                            <option value="{{ $y }}" {{ (request('tahun') ?? date('Y')) == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            {{-- Filter Seksi --}}
                            <div class="w-full sm:w-64">
                                <label for="seksi" class="block text-xs font-semibold text-gray-500 mb-1 ml-1">Seksi / Jabatan</label>
                                <div class="relative">
                                    <i class="fa-solid fa-sitemap absolute left-3 top-2.5 text-gray-400 pointer-events-none"></i>
                                    <select name="seksi" id="seksi" onchange="this.form.submit()" 
                                            class="pl-9 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm cursor-pointer bg-gray-50 hover:bg-white transition-colors">
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
                                    <a href="{{ route('laporan.index') }}" class="text-sm text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors flex items-center gap-2" title="Hapus Filter">
                                        <i class="fa-solid fa-rotate-left"></i> <span class="hidden sm:inline">Reset</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
{{-- DATA CONTENT --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
                
                @foreach($jabatans as $jabatan)
                    @if($jabatan->users->isNotEmpty())
                    
                    @php
                        $jumlahPegawai = $jabatan->users->count();
                        $namaJabatan = $jabatan->nama_jabatan;

                        // Cek apakah jabatan adalah Kepala Kantor atau Kepala Seksi (Pejabat)
                        $isPejabat = \Illuminate\Support\Str::contains($namaJabatan, ['Kepala Kantor', 'Kepala Seksi']);

                        if ($isPejabat) {
                            // --- SETTING KHUSUS PEJABAT (Full Width & Besar) ---
                            $colSpanClass = 'xl:col-span-2'; 
                            $innerGridClass = 'flex flex-wrap justify-center gap-6';
                            $cardSizeClass = 'w-full max-w-2xl min-h-[260px]'; 
                            $paddingClass = 'p-8';
                            $avatarSize = 'h-14 w-14 text-xl'; 
                            $nameSize = 'text-xl'; 
                            
                        } else {
                            // --- SETTING STAFF BIASA ---
                            
                            // LOGIKA WADAH UTAMA:
                            // 1 org -> Setengah Lebar ('xl:col-span-1') -> Tampil Berdampingan
                            // >1 org -> Full Lebar ('xl:col-span-2') -> Tampil Sendiri
                            $colSpanClass = $jumlahPegawai === 1 ? 'xl:col-span-1' : 'xl:col-span-2';
                            
                            // LOGIKA GRID PEGAWAI:
                            // [PERBAIKAN] Jika 1 orang -> 'grid-cols-1' 
                            // (Agar kartu memenuhi wadah berdampingan, TIDAK dikecilkan/dibagi dua)
                            $innerGridClass = $jumlahPegawai === 1 ? 'grid gap-4 grid-cols-1' : 'grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
                            
                            // Ukuran standar untuk staff
                            $cardSizeClass = 'h-full min-h-[200px]';
                            $paddingClass = 'p-5';
                            $avatarSize = 'h-10 w-10 text-base';
                            $nameSize = 'text-sm';
                        }
                    @endphp

                    {{-- Container Jabatan --}}
                    <div class="w-full bg-white/50 rounded-2xl p-4 border border-transparent hover:border-gray-200 transition-colors {{ $colSpanClass }}">
                        
                        {{-- Header Jabatan --}}
                        <div class="flex items-center gap-3 mb-4 pb-2 border-b-2 border-gray-100 {{ $isPejabat ? 'justify-center' : '' }}">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                            <h3 class="text-lg font-bold text-gray-800">
                                {{ $jabatan->nama_jabatan }}
                            </h3>
                            @if(!$isPejabat) 
                                <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full ml-auto">
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
                                    $sisa = $user->sisa_berkas;

                                    $total_beban = $keluar + $sisa;
                                    $persen = $total_beban > 0 ? round(($keluar / $total_beban) * 100) : 0;
                                    
                                    $warnaText = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                    $bgBar = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                    
                                    $harian = $user->produktivitas_harian;
                                @endphp

                                {{-- Card Pegawai --}}
                                <div class="{{ $cardSizeClass }} bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 hover:border-indigo-100 transition-all duration-300 relative group flex flex-col">
                                    <div class="{{ $paddingClass }} flex-1 flex flex-col">
                                        
                                        {{-- Badge Harian --}}
                                        <div class="absolute top-3 right-3">
                                            @if($harian > 0)
                                                <span class="bg-green-50 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-green-200 flex items-center gap-1">
                                                    <i class="fa-solid fa-check-double"></i> +{{ $harian }}
                                                </span>
                                            @else
                                                <span class="bg-gray-50 text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-gray-200">
                                                    0
                                                </span>
                                            @endif
                                        </div>

                                        {{-- User Info --}}
                                        <div class="flex items-center gap-4 mb-4 pr-16">
                                            <div class="{{ $avatarSize }} rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold border border-indigo-100 shadow-sm flex-shrink-0">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-gray-800 {{ $nameSize }} truncate" title="{{ $user->name }}">
                                                    {{ $user->name }}
                                                </h4>
                                                <p class="text-[10px] text-gray-500 uppercase tracking-wide truncate">
                                                    {{ $jabatan->nama_jabatan }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-50 my-3"></div>

                                        {{-- Stats Grid --}}
                                        <div class="grid grid-cols-3 gap-2 mb-4">
                                            <div class="bg-blue-50 p-2 rounded-lg text-center">
                                                <span class="block text-[10px] font-bold text-blue-500 uppercase">Masuk</span>
                                                <span class="block text-lg font-black text-blue-700">{{ $masuk }}</span>
                                            </div>
                                            <div class="bg-green-50 p-2 rounded-lg text-center">
                                                <span class="block text-[10px] font-bold text-green-500 uppercase">Selesai</span>
                                                <span class="block text-lg font-black text-green-700">{{ $keluar }}</span>
                                            </div>
                                            <div class="bg-orange-50 p-2 rounded-lg text-center">
                                                <span class="block text-[10px] font-bold text-orange-500 uppercase">Sisa</span>
                                                <span class="block text-lg font-black text-orange-700">{{ $sisa }}</span>
                                            </div>
                                        </div>

                                        {{-- Progress Bar --}}
                                        <div class="mt-auto">
                                            <div class="flex justify-between items-end mb-1">
                                                <span class="text-[10px] font-medium text-gray-500">Kinerja Th. {{ request('tahun', date('Y')) }}</span>
                                                <span class="text-xs font-black {{ $warnaText }}">{{ $persen }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $bgBar }}" style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                        
                                        {{-- Link Detail --}}
                                        <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => request('tahun', date('Y'))]) }}" 
                                           class="absolute inset-0 z-10 rounded-xl" 
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