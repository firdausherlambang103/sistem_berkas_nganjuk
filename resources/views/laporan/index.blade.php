<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- FILTER SECTION (DIPERBAIKI) --}}
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

                            {{-- Tombol Reset (Hanya muncul jika difilter) --}}
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
            @foreach($jabatans as $jabatan)
                @if($jabatan->users->isNotEmpty())
                <div class="mb-12">
                    {{-- Header Jabatan --}}
                    <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-gray-100">
                        <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ $jabatan->nama_jabatan }}
                        </h3>
                    </div>

                    {{-- Grid Pegawai --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jabatan->users as $user)
                            @php
                                $masuk = $user->total_masuk;
                                $keluar = $user->total_keluar;
                                $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
                                
                                $warnaText = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                $bgBar = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                
                                $harian = $user->produktivitas_harian;
                            @endphp

                            {{-- Card Pegawai (h-full untuk tinggi sama rata) --}}
                            <div class="h-full bg-white rounded-xl shadow-sm hover:shadow-lg border border-gray-100 hover:border-indigo-100 transition-all duration-300 relative group flex flex-col">
                                <div class="p-6 flex-1 flex flex-col">
                                    
                                    {{-- Badge Harian (Pojok Kanan Atas) --}}
                                    <div class="absolute top-4 right-4">
                                        @if($harian > 0)
                                            <span class="bg-green-50 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-green-200 flex items-center gap-1">
                                                <i class="fa-solid fa-check-double"></i> +{{ $harian }} Hari Ini
                                            </span>
                                        @else
                                            <span class="bg-gray-50 text-gray-400 text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-200">
                                                0 Hari Ini
                                            </span>
                                        @endif
                                    </div>

                                    {{-- User Info --}}
                                    <div class="flex items-center gap-4 mb-6 pr-20">
                                        <div class="h-12 w-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg border border-indigo-100 shadow-sm flex-shrink-0">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-gray-800 text-base truncate" title="{{ $user->name }}">
                                                {{ $user->name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide truncate">
                                                {{ $jabatan->nama_jabatan }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-50 my-4"></div>

                                    {{-- Stats Grid --}}
                                    <div class="grid grid-cols-3 gap-3 mb-6">
                                        <div class="bg-blue-50 p-2.5 rounded-lg text-center">
                                            <span class="block text-[10px] font-bold text-blue-500 uppercase mb-1">Masuk</span>
                                            <span class="block text-xl font-black text-blue-700">{{ $masuk }}</span>
                                        </div>
                                        <div class="bg-green-50 p-2.5 rounded-lg text-center">
                                            <span class="block text-[10px] font-bold text-green-500 uppercase mb-1">Selesai</span>
                                            <span class="block text-xl font-black text-green-700">{{ $keluar }}</span>
                                        </div>
                                        <div class="bg-orange-50 p-2.5 rounded-lg text-center">
                                            <span class="block text-[10px] font-bold text-orange-500 uppercase mb-1">Sisa</span>
                                            <span class="block text-xl font-black text-orange-700">{{ $user->sisa_berkas }}</span>
                                        </div>
                                    </div>

                                    {{-- Progress Bar (Footer Card) --}}
                                    <div class="mt-auto">
                                        <div class="flex justify-between items-end mb-1.5">
                                            <span class="text-xs font-medium text-gray-500">Kinerja Tahun {{ request('tahun', date('Y')) }}</span>
                                            <span class="text-sm font-black {{ $warnaText }}">{{ $persen }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $bgBar }} transition-all duration-1000 ease-out" style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>
                                    
                                    {{-- Tombol Detail (Hover Overlay or Bottom) --}}
                                    <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => request('tahun', date('Y'))]) }}" 
                                       class="absolute inset-0 z-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" 
                                       title="Lihat Detail Kinerja {{ $user->name }}">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

        </div>
    </div>
</x-app-layout>