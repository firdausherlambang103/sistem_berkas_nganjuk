<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center">
                 <a href="{{ route('laporan.index', ['tahun' => request('tahun', date('Y'))]) }}" class="text-gray-400 hover:text-gray-600 mr-4 transition" title="Kembali ke Laporan Rinci">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Daftar Berkas: <span class="text-indigo-600">{{ $petugas->name }}</span>
                </h2>
            </div>

            {{-- FILTER TAHUN --}}
            <form method="GET" action="{{ route('laporan.berkas_by_user', $petugas->id) }}" class="flex items-center bg-white px-3 py-1.5 rounded-md shadow-sm border border-gray-200">
                <label for="tahun" class="text-sm font-medium text-gray-700 flex items-center">
                    <i class="fa-regular fa-calendar-days mr-2 text-indigo-500"></i> Tahun:
                </label>
                <select name="tahun" id="tahun" onchange="this.form.submit()" class="border-none focus:ring-0 text-sm font-bold text-gray-700 bg-transparent py-0 pl-2 pr-8 cursor-pointer">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Statistik Ringkas Petugas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="text-xs text-gray-500 font-bold uppercase">Total Masuk</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $totalMasuk }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 font-bold uppercase">Total Selesai/Keluar</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $totalKeluar }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-orange-500">
                    <div class="text-xs text-gray-500 font-bold uppercase">Sisa (Pending)</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $sisaBerkas }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="text-xs text-gray-500 font-bold uppercase">Produktivitas</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $persentasePenyelesaian }}%</div>
                </div>
            </div>

            {{-- Tabel Daftar Berkas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-bold text-lg mb-4 text-gray-700">Berkas yang Sedang Dikerjakan (Tahun {{ request('tahun', date('Y')) }})</h3>
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
                                        // Asumsi: di Model Berkas sudah ada accessor getJatuhTempoAttribute() dan getSisaWaktuAttribute()
                                        // Jika belum, logika hitung tanggal bisa dilakukan di sini
                                        $jatuhTempo = null;
                                        if($berkas->jenisPermohonan && $berkas->waktu_mulai_proses) {
                                            $jatuhTempo = \Carbon\Carbon::parse($berkas->waktu_mulai_proses)
                                                ->addDays($berkas->jenisPermohonan->waktu_timeline_hari);
                                        }
                                        
                                        $isLewat = $jatuhTempo && \Carbon\Carbon::now()->greaterThan($jatuhTempo);
                                    @endphp
                                    <tr class="{{ $isLewat ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}">
                                        <td class="px-6 py-4 whitespace-nowrap {{ $isLewat ? 'border-l-4 border-red-400' : '' }}">
                                            <a href="{{ route('berkas.show', $berkas) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold hover:underline">
                                                {{ $berkas->nomer_berkas }}
                                            </a>
                                            {{-- Tampilan 2 baris untuk nama pemohon --}}
                                            <p class="text-sm text-gray-800 font-medium">{{ $berkas->nama_pemohon }}</p>
                                            <p class="text-xs text-gray-500">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'Tidak Ada' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{-- Menampilkan waktu mulai argo yang sudah dihitung di Controller --}}
                                            {{ $berkas->waktu_mulai_argo ? \Carbon\Carbon::parse($berkas->waktu_mulai_argo)->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($jatuhTempo)
                                                <p class="text-sm font-semibold {{ $isLewat ? 'text-red-600' : 'text-gray-800' }}">
                                                    {{ $jatuhTempo->isoFormat('D MMM YYYY') }}
                                                </p>
                                                <p class="text-xs {{ $isLewat ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                                                    @if($isLewat)
                                                        Telat {{ $jatuhTempo->diffForHumans() }}
                                                    @else
                                                        {{ $jatuhTempo->diffForHumans() }}
                                                    @endif
                                                </p>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-400 flex flex-col items-center justify-center">
                                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                            <span>Tidak ada berkas aktif pada petugas ini di tahun {{ request('tahun', date('Y')) }}.</span>
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