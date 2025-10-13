<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-chart-line mr-2"></i>
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Kartu Statistik Utama (Semua bisa diklik) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('dashboard.total') }}" class="bg-blue-500 text-white p-6 rounded-lg shadow-lg flex items-center justify-between hover:bg-blue-600 transition-colors">
                    <div>
                        <h3 class="text-sm font-medium uppercase">Total Berkas</h3>
                        <p class="mt-1 text-4xl font-bold">{{ $totalBerkas }}</p>
                    </div>
                    <i class="fa-solid fa-file-alt text-5xl opacity-50"></i>
                </a>
                <a href="{{ route('dashboard.diproses') }}" class="bg-yellow-500 text-white p-6 rounded-lg shadow-lg flex items-center justify-between hover:bg-yellow-600 transition-colors">
                    <div>
                        <h3 class="text-sm font-medium uppercase">Sedang Diproses</h3>
                        <p class="mt-1 text-4xl font-bold">{{ $totalDiproses }}</p>
                    </div>
                    <i class="fa-solid fa-hourglass-half text-5xl opacity-50"></i>
                </a>
                <a href="{{ route('dashboard.jatuh-tempo') }}" class="bg-red-600 text-white p-6 rounded-lg shadow-lg flex items-center justify-between hover:bg-red-700 transition-colors">
                    <div>
                        <h3 class="text-sm font-medium uppercase">Jatuh Tempo</h3>
                        <p class="mt-1 text-4xl font-bold">{{ $berkasJatuhTempoCount }}</p>
                    </div>
                    <i class="fa-solid fa-calendar-times text-5xl opacity-50"></i>
                </a>
                <a href="{{ route('dashboard.selesai') }}" class="bg-green-500 text-white p-6 rounded-lg shadow-lg flex items-center justify-between hover:bg-green-600 transition-colors">
                    <div>
                        <h3 class="text-sm font-medium uppercase">Selesai</h3>
                        <p class="mt-1 text-4xl font-bold">{{ $totalSelesai }}</p>
                    </div>
                    <i class="fa-solid fa-check-circle text-5xl opacity-50"></i>
                </a>
            </div>

            {{-- Tabel Rincian Berkas --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-bold text-gray-800">Rincian Berkas Saat Ini</h3>
                        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center space-x-2">
                            <input type="text" name="search" placeholder="Cari no berkas, pemohon..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ request('search') }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Cari</button>
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail Pemohon & Hak</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Permohonan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Posisi / Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lama Proses</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($semuaBerkasAktif as $berkas)
                                    @php
                                        $isLewat = $berkas->jatuh_tempo && \Carbon\Carbon::now()->greaterThan($berkas->jatuh_tempo);
                                    @endphp
                                    <tr class="hover:bg-gray-100 {{ $isLewat ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap {{ $isLewat ? 'border-l-4 border-red-400' : '' }}">
                                            <p class="text-sm font-semibold text-gray-800">{{ $berkas->nomer_berkas }}</p>
                                        </td>
                                        {{-- PERUBAHAN TATA LETAK 3 BARIS DI SINI --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-gray-900">{{ $berkas->nama_pemohon }}</p>
                                            <p class="text-xs text-gray-500">{{ $berkas->jenis_alas_hak }} / {{ $berkas->nomer_hak }}</p>
                                            <p class="text-xs text-gray-500">{{ $berkas->desa }}, {{ $berkas->kecamatan }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-normal">
                                            <p class="text-sm font-semibold text-gray-800">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($berkas->status == 'Pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Ditunda
                                                </span>
                                            @else
                                                <div class="text-sm text-gray-900">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">{{ optional($berkas->posisiSekarang)->name ?? 'N/A' }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $berkas->lama_proses_formatted }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($berkas->jatuh_tempo)
                                                <p class="text-sm font-semibold {{ $isLewat ? 'text-red-600' : 'text-gray-800' }}">
                                                    {{ $berkas->jatuh_tempo->isoFormat('D MMM YYYY') }}
                                                </p>
                                                <p class="text-xs {{ $isLewat ? 'text-red-500' : 'text-gray-500' }}">
                                                    ({{ $berkas->sisa_waktu }})
                                                </p>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('berkas.show', $berkas) }}" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md hover:bg-gray-700">
                                                <i class="fa-solid fa-eye mr-2"></i> Riwayat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            @if(request('search'))
                                                Berkas dengan kata kunci "{{ request('search') }}" tidak ditemukan.
                                            @else
                                                Tidak ada berkas yang sedang diproses.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $semuaBerkasAktif->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

