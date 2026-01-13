<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center w-full lg:w-auto">
                <i class="fa-solid fa-chart-line mr-2 text-indigo-500"></i>
                {{ __('Dashboard') }}
            </h2>
            
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                
                {{-- PENCARIAN BERKAS (Action ke route dashboard) --}}
                <form method="GET" action="{{ route('dashboard') }}" class="relative w-full sm:w-64">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Berkas / Nama..." 
                           class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm transition-colors"
                           autocomplete="off">
                </form>

                {{-- TOMBOL LIHAT SEMUA (Mode 'all' ke route dashboard) --}}
                <a href="{{ route('dashboard', ['tahun' => $tahun, 'mode' => 'all']) }}" 
                   class="w-full sm:w-auto whitespace-nowrap inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                   title="Lihat Data Lengkap Tahun {{ $tahun }}">
                    <i class="fa-solid fa-list-ul mr-2"></i> Lihat Semua
                </a>

                {{-- FILTER TAHUN --}}
                <form method="GET" action="{{ route('dashboard') }}" class="w-full sm:w-auto flex items-center bg-white px-3 py-2 rounded-lg shadow-sm border border-gray-300">
                    <label for="tahun" class="text-sm font-medium text-gray-500 flex items-center whitespace-nowrap cursor-pointer">
                        <i class="fa-regular fa-calendar-days mr-2 text-indigo-500"></i>
                    </label>
                    <select name="tahun" id="tahun" onchange="this.form.submit()" class="border-none focus:ring-0 text-sm font-bold text-gray-700 bg-transparent py-0 pl-1 pr-8 cursor-pointer w-full sm:w-auto focus:outline-none">
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Info Banner --}}
            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-info text-indigo-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-indigo-700">
                            Menampilkan data statistik untuk Tahun Anggaran <strong>{{ $tahun }}</strong>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Kartu Statistik Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Berkas --}}
                <a href="{{ route('dashboard.total', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-blue-500 group relative">
                    <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fa-solid fa-folder-open text-6xl text-blue-500"></i>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-blue-600 transition-colors">Total Berkas</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalBerkas }}</div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-full text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-all shadow-sm">
                            <i class="fa-solid fa-folder-open text-xl"></i>
                        </div>
                    </div>
                </a>

                {{-- Sedang Diproses --}}
                <a href="{{ route('dashboard.diproses', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-yellow-500 group relative">
                    <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fa-solid fa-spinner text-6xl text-yellow-500"></i>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-yellow-600 transition-colors">Sedang Diproses</div>
                            <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $totalDiproses }}</div>
                        </div>
                        <div class="p-3 bg-yellow-50 rounded-full text-yellow-500 group-hover:bg-yellow-500 group-hover:text-white transition-all shadow-sm">
                            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                        </div>
                    </div>
                </a>

                {{-- Jatuh Tempo --}}
                <a href="{{ route('dashboard.jatuh-tempo', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-red-500 group relative">
                    <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fa-solid fa-calendar-times text-6xl text-red-500"></i>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-red-600 transition-colors">Jatuh Tempo</div>
                            <div class="mt-2 text-3xl font-bold text-red-600">{{ $berkasJatuhTempoCount }}</div>
                        </div>
                        <div class="p-3 bg-red-50 rounded-full text-red-500 group-hover:bg-red-500 group-hover:text-white transition-all shadow-sm">
                            <i class="fa-solid fa-calendar-times text-xl"></i>
                        </div>
                    </div>
                </a>

                {{-- Selesai --}}
                <a href="{{ route('dashboard.selesai', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-green-500 group relative">
                    <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fa-solid fa-check-circle text-6xl text-green-500"></i>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-green-600 transition-colors">Selesai</div>
                            <div class="mt-2 text-3xl font-bold text-green-600">{{ $totalSelesai }}</div>
                        </div>
                        <div class="p-3 bg-green-50 rounded-full text-green-500 group-hover:bg-green-500 group-hover:text-white transition-all shadow-sm">
                            <i class="fa-solid fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Tabel Berkas Terbaru (Hanya 5 Data) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <i class="fa-regular fa-clock text-indigo-500"></i> Berkas Terbaru ({{ $tahun }})
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Menampilkan 5 berkas terakhir yang dibuat pada tahun ini.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Hak & No</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Posisi / Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($berkasTerbaru as $berkas)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-indigo-600">{{ $berkas->nomer_berkas }}</div>
                                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <i class="fa-regular fa-clock text-[10px]"></i> {{ $berkas->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $berkas->nama_pemohon }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($berkas->desa, 20) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-700">{{ $berkas->jenis_alas_hak }}</div>
                                            <div class="text-xs font-mono text-gray-500 bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200 mt-1">
                                                {{ $berkas->nomer_hak }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ optional($berkas->posisiSekarang)->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @php
                                                $badgeClass = match($berkas->status) {
                                                    'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                                                    'Ditutup' => 'bg-red-100 text-red-800 border-red-200',
                                                    'Pending' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                    default => 'bg-blue-100 text-blue-800 border-blue-200',
                                                };
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }} shadow-sm">
                                                {{ $berkas->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('berkas.show', $berkas->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-all duration-200 border border-transparent hover:border-indigo-100" title="Lihat Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            <span class="font-medium">Belum ada data berkas untuk tahun {{ $tahun }}.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- [BARU] Bagian Hasil Pencarian / Lihat Semua Data --}}
            @if(isset($additionalBerkas) && $additionalBerkas)
            <div id="hasil-pencarian" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 animate-fade-in-up">
                <div class="p-6 border-b border-gray-200 bg-indigo-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                                @if($searchQuery)
                                    <i class="fa-solid fa-magnifying-glass"></i> Hasil Pencarian: "{{ $searchQuery }}"
                                @else
                                    <i class="fa-solid fa-list-check"></i> Semua Data Berkas ({{ $tahun }})
                                @endif
                            </h3>
                            <p class="text-sm text-indigo-600 mt-1">
                                Menampilkan {{ $additionalBerkas->total() }} data ditemukan.
                            </p>
                        </div>
                        {{-- Tombol Tutup / Reset --}}
                        <a href="{{ route('dashboard', ['tahun' => $tahun]) }}" class="text-sm text-gray-500 hover:text-red-600 hover:underline">
                            <i class="fa-solid fa-xmark mr-1"></i> Tutup Tampilan Ini
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Hak & No</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Posisi / Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($additionalBerkas as $berkas)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-indigo-600">{{ $berkas->nomer_berkas }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $berkas->created_at->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $berkas->nama_pemohon }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($berkas->desa, 20) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-700">{{ $berkas->jenis_alas_hak }}</div>
                                            <div class="text-xs font-mono text-gray-500 bg-gray-100 inline-block px-1.5 py-0.5 rounded border border-gray-200 mt-1">
                                                {{ $berkas->nomer_hak }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @php
                                                $badgeClass = match($berkas->status) {
                                                    'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                                                    'Ditutup' => 'bg-red-100 text-red-800 border-red-200',
                                                    'Pending' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                    default => 'bg-blue-100 text-blue-800 border-blue-200',
                                                };
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                                {{ $berkas->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('berkas.show', $berkas->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            Tidak ada data ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $additionalBerkas->links() }}
                    </div>
                </div>
            </div>
            
            {{-- Script Auto Scroll ke Hasil Pencarian --}}
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var element = document.getElementById("hasil-pencarian");
                    if(element) {
                        element.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                });
            </script>
            @endif

        </div>
    </div>
</x-app-layout>