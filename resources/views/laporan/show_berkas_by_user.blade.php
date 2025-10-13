<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ route('laporan.index') }}" class="text-gray-400 hover:text-gray-600 mr-4" title="Kembali ke Laporan Rinci">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Daftar Berkas pada: <span class="text-indigo-600">{{ $petugas->name }}</span>
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail Berkas & Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Mulai Argo</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($daftarBerkas as $berkas)
                                    @php
                                        // Variabel untuk mengecek apakah sudah jatuh tempo
                                        $isLewat = $berkas->jatuh_tempo && \Carbon\Carbon::now()->greaterThan($berkas->jatuh_tempo);
                                    @endphp
                                    <tr class="{{ $isLewat ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}">
                                        <td class="px-6 py-4 whitespace-nowrap {{ $isLewat ? 'border-l-4 border-red-400' : '' }}">
                                            <a href="{{ route('berkas.show', $berkas) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold hover:underline">
                                                {{ $berkas->nomer_berkas }}
                                            </a>
                                            {{-- Tampilan 2 baris untuk nama pemohon --}}
                                            <p class="text-sm text-gray-800">{{ $berkas->nama_pemohon }}</p>
                                            <p class="text-xs text-gray-500">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'Tidak Ada' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{-- Menampilkan waktu mulai argo yang sudah dihitung di Controller --}}
                                            {{ \Carbon\Carbon::parse($berkas->waktu_mulai_argo)->format('d M Y, H:i') }}
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada berkas pada petugas ini.</td>
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

