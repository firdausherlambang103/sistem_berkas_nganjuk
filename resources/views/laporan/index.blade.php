<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- FILTER SECTION --}}
            <div class="mb-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
                    
                    {{-- Judul Filter --}}
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <i class="fa-solid fa-filter text-indigo-500"></i>
                        <span class="font-bold text-gray-700">Filter Data</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                        {{-- Filter Tahun --}}
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="tahun" class="text-sm font-medium text-gray-600 whitespace-nowrap">Tahun:</label>
                            <select name="tahun" id="tahun" onchange="this.form.submit()" 
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-full sm:w-32 cursor-pointer bg-gray-50">
                                @for($y = date('Y'); $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ (request('tahun') ?? date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Filter Seksi --}}
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="seksi" class="text-sm font-medium text-gray-600 whitespace-nowrap">Seksi:</label>
                            <select name="seksi" id="seksi" onchange="this.form.submit()" 
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-full sm:w-64 cursor-pointer bg-gray-50">
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
                    @if(request('seksi') || request('tahun'))
                        <div class="w-full md:w-auto text-right">
                            <a href="{{ route('laporan.index') }}" class="text-sm text-red-600 hover:text-red-800 hover:underline flex items-center justify-end gap-1 transition">
                                <i class="fa-solid fa-rotate-left"></i> Reset Filter
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            {{-- DATA CONTENT --}}
            @foreach($jabatans as $jabatan)
                @if($jabatan->users->isNotEmpty())
                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-200 pb-2">
                        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ $jabatan->nama_jabatan }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jabatan->users as $user)
                            @php
                                // Hitung Performa Total (Presentase)
                                $masuk = $user->total_masuk;
                                $keluar = $user->total_keluar;
                                $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
                                
                                // Tentukan warna performa total
                                $warnaRing = $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600');
                                $bgBar = $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                
                                // Ambil Produktivitas Harian
                                $harian = $user->produktivitas_harian;
                            @endphp

                            <div class="bg-white overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 rounded-xl border border-gray-100 relative group">
                                <div class="p-6">
                                    {{-- Badge Harian --}}
                                    <div class="absolute top-4 right-4">
                                        <span class="bg-green-50 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-green-200 shadow-sm flex items-center gap-1">
                                            <i class="fa-solid fa-check-double"></i> Hari Ini: {{ $harian }}
                                        </span>
                                    </div>

                                    {{-- User Info --}}
                                    <div class="flex items-center mb-6 pr-16">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-md mr-4">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm md:text-base leading-tight">{{ $user->name }}</h4>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">{{ $jabatan->nama_jabatan }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Tombol Detail --}}
                                    <div class="flex justify-end mb-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 absolute top-14 right-4">
                                         <a href="{{ route('laporan.berkas_by_user', ['user' => $user->id, 'tahun' => request('tahun', date('Y'))]) }}" 
                                            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-full hover:bg-indigo-700 shadow-sm flex items-center gap-1">
                                            Detail <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>

                                    <div class="h-px bg-gray-100 mb-4"></div>

                                    {{-- Stats Grid --}}
                                    <div class="grid grid-cols-3 gap-2 text-center mb-5">
                                        <div class="bg-blue-50 p-2 rounded-lg">
                                            <span class="block text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-1">Masuk</span>
                                            <span class="block text-xl font-black text-blue-800">{{ $masuk }}</span>
                                        </div>
                                        <div class="bg-green-50 p-2 rounded-lg">
                                            <span class="block text-[10px] text-green-600 font-bold uppercase tracking-wider mb-1">Selesai</span>
                                            <span class="block text-xl font-black text-green-800">{{ $keluar }}</span>
                                        </div>
                                        <div class="bg-orange-50 p-2 rounded-lg">
                                            <span class="block text-[10px] text-orange-600 font-bold uppercase tracking-wider mb-1">Pending</span>
                                            <span class="block text-xl font-black text-orange-800">{{ $user->sisa_berkas }}</span>
                                        </div>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div>
                                        <div class="flex justify-between items-end mb-1.5">
                                            <span class="text-xs font-semibold text-gray-500">Produktivitas Tahun {{ request('tahun', date('Y')) }}</span>
                                            <span class="text-sm font-black {{ $warnaRing }}">{{ $persen }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                            <div class="h-2.5 rounded-full {{ $bgBar }} transition-all duration-1000 ease-out" style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>

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