<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <i class="fa-solid fa-chart-line mr-2 text-indigo-500"></i>
                {{ __('Dashboard') }}
            </h2>
            
            {{-- FILTER TAHUN DASHBOARD --}}
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-md shadow-sm border border-gray-200">
                <label for="tahun" class="text-sm font-medium text-gray-700 flex items-center">
                    <i class="fa-regular fa-calendar-days mr-2 text-indigo-500"></i> Tahun:
                </label>
                <select name="tahun" id="tahun" onchange="this.form.submit()" class="border-none focus:ring-0 text-sm font-bold text-gray-700 bg-transparent py-0 pl-2 pr-8 cursor-pointer">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Info Banner --}}
            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded shadow-sm flex justify-between items-center">
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
                <a href="{{ route('dashboard.total', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-blue-500 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-blue-600 transition-colors">Total Berkas</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalBerkas }}</div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-full text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-folder-open text-xl"></i>
                        </div>
                    </div>
                </a>

                <a href="{{ route('dashboard.diproses', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-yellow-500 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-yellow-600 transition-colors">Sedang Diproses</div>
                            <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $totalDiproses }}</div>
                        </div>
                        <div class="p-3 bg-yellow-50 rounded-full text-yellow-500 group-hover:bg-yellow-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                        </div>
                    </div>
                </a>

                <a href="{{ route('dashboard.jatuh-tempo', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-red-500 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-red-600 transition-colors">Jatuh Tempo</div>
                            <div class="mt-2 text-3xl font-bold text-red-600">{{ $berkasJatuhTempoCount }}</div>
                        </div>
                        <div class="p-3 bg-red-50 rounded-full text-red-500 group-hover:bg-red-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-calendar-times text-xl"></i>
                        </div>
                    </div>
                </a>

                <a href="{{ route('dashboard.selesai', ['tahun' => $tahun]) }}" class="block bg-white overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 sm:rounded-lg p-6 border-b-4 border-green-500 group">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-xs font-bold uppercase tracking-widest group-hover:text-green-600 transition-colors">Selesai</div>
                            <div class="mt-2 text-3xl font-bold text-green-600">{{ $totalSelesai }}</div>
                        </div>
                        <div class="p-3 bg-green-50 rounded-full text-green-500 group-hover:bg-green-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Tabel Rincian Berkas (Berkas Terbaru Tahun Terpilih) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Berkas Terbaru ({{ $tahun }})</h3>
                            <p class="text-sm text-gray-500">Menampilkan 5 berkas terakhir yang dibuat pada tahun ini.</p>
                        </div>
                        
                        {{-- Opsi Pencarian jika diperlukan (Mengarah ke Laporan untuk pencarian lebih detail) --}}
                        <a href="{{ route('laporan.index', ['tahun' => $tahun]) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                            Lihat Semua Data <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
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
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-indigo-600">{{ $berkas->nomer_berkas }}</div>
                                            <div class="text-xs text-gray-500 mt-1"><i class="fa-regular fa-clock mr-1"></i> {{ $berkas->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $berkas->nama_pemohon }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($berkas->desa, 15) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-700">{{ $berkas->jenis_alas_hak }}</div>
                                            <div class="text-xs font-mono text-gray-500 bg-gray-50 inline-block px-1 rounded">{{ $berkas->nomer_hak }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? '-' }}</div>
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
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                                {{ $berkas->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('berkas.show', $berkas->id) }}" class="text-gray-400 hover:text-indigo-600 transition" title="Lihat Detail">
                                                <i class="fa-solid fa-eye text-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic flex flex-col items-center justify-center">
                                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                            <span>Belum ada data berkas untuk tahun {{ $tahun }}.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>