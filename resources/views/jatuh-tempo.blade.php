<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 mr-4" title="Kembali ke Dashboard">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-calendar-times text-red-600 mr-2"></i>
                Daftar Berkas Jatuh Tempo
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    {{-- PERUBAHAN: Nama kolom disesuaikan --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail Berkas & Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi Terakhir</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasJatuhTempo as $berkas)
                                    <tr class="bg-red-50 hover:bg-red-100">
                                        {{-- PERUBAHAN UTAMA: Menggabungkan beberapa kolom menjadi satu --}}
                                        <td class="px-6 py-4 whitespace-normal border-l-4 border-red-400">
                                            <a href="{{ route('berkas.show', $berkas) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 hover:underline">
                                                {{ $berkas->nomer_berkas }}
                                            </a>
                                            <p class="text-sm font-medium text-gray-800">{{ $berkas->nama_pemohon }}</p>
                                            <p class="text-xs text-gray-500">{{ $berkas->jenis_alas_hak }} / {{ $berkas->nomer_hak }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-gray-900">{{ optional($berkas->posisiSekarang)->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ optional(optional($berkas->posisiSekarang)->jabatan)->nama_jabatan ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-red-600">
                                                {{ $berkas->jatuh_tempo->isoFormat('D MMM YYYY') }}
                                            </p>
                                            <p class="text-xs text-red-500">
                                                ({{ $berkas->sisa_waktu }})
                                            </p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada berkas yang melewati jatuh tempo saat ini.
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

