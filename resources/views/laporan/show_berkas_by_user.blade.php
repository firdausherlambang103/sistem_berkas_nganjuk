<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <a href="{{ route('laporan.index', ['tahun' => $tahun]) }}" class="mr-3 text-gray-400 hover:text-indigo-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                {{ __('Detail Kinerja Pegawai') }}
            </h2>
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm text-sm text-gray-600">
                <i class="fa-regular fa-calendar-days text-indigo-500"></i>
                <span>Tahun Anggaran: <strong>{{ $tahun }}</strong></span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. PROFIL & STATISTIK UTAMA --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 flex flex-col md:flex-row gap-8 items-center md:items-start">
                    
                    {{-- Profil Pegawai --}}
                    <div class="flex flex-col items-center md:items-start text-center md:text-left min-w-[200px]">
                        <div class="h-24 w-24 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-4xl font-bold border-4 border-white shadow-lg mb-4">
                            {{ substr($petugas->name, 0, 1) }}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $petugas->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ optional($petugas->jabatan)->nama_jabatan ?? 'Pegawai' }}</p>
                        
                        {{-- Progress Bar Ringkas --}}
                        <div class="w-full mt-4 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $persentasePenyelesaian }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 font-medium">Penyelesaian: {{ $persentasePenyelesaian }}%</p>
                    </div>

                    {{-- Perhitungan Statistik Real-time --}}
                    @php
                        // Filter manual collection
                        $jmlProses = $daftarBerkas->where('status', 'Diproses')->count();
                        $jmlPending = $daftarBerkas->where('status', 'Pending')->count();
                        
                        // Hitung Jatuh Tempo (HANYA DARI BERKAS AKTIF)
                        $jmlJatuhTempo = $daftarBerkas->filter(function ($item) {
                            if (!$item->jenisPermohonan) return false;
                            
                            $timeline = $item->jenisPermohonan->waktu_timeline_hari;
                            $mulai = $item->waktu_mulai_proses ? \Carbon\Carbon::parse($item->waktu_mulai_proses) : $item->created_at;
                            $deadline = $mulai->copy()->addDays($timeline);
                            
                            return now()->greaterThan($deadline);
                        })->count();
                    @endphp

                    {{-- Grid Statistik 5 Kolom --}}
                    <div class="flex-1 w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        
                        {{-- Masuk --}}
                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex flex-col justify-between">
                            <div class="text-blue-600 text-xs font-bold uppercase tracking-wide">Total Masuk</div>
                            <div class="text-3xl font-black text-blue-800 mt-2">{{ $totalMasuk }}</div>
                            <div class="text-[10px] text-blue-400 mt-1">Berkas diterima</div>
                        </div>

                        {{-- Proses --}}
                        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 flex flex-col justify-between">
                            <div class="text-yellow-600 text-xs font-bold uppercase tracking-wide">Diproses</div>
                            <div class="text-3xl font-black text-yellow-800 mt-2">{{ $jmlProses }}</div>
                            <div class="text-[10px] text-yellow-500 mt-1">Sedang dikerjakan</div>
                        </div>

                        {{-- Pending --}}
                        <div class="bg-orange-50 rounded-xl p-4 border border-orange-100 flex flex-col justify-between">
                            <div class="text-orange-600 text-xs font-bold uppercase tracking-wide">Pending</div>
                            <div class="text-3xl font-black text-orange-800 mt-2">{{ $jmlPending }}</div>
                            <div class="text-[10px] text-orange-400 mt-1">Ditunda sementara</div>
                        </div>

                        {{-- Jatuh Tempo --}}
                        <div class="bg-red-50 rounded-xl p-4 border border-red-100 flex flex-col justify-between relative overflow-hidden">
                            @if($jmlJatuhTempo > 0)
                                <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-bl-lg font-bold animate-pulse">!</div>
                            @endif
                            <div class="text-red-600 text-xs font-bold uppercase tracking-wide">Jatuh Tempo</div>
                            <div class="text-3xl font-black text-red-800 mt-2">{{ $jmlJatuhTempo }}</div>
                            <div class="text-[10px] text-red-400 mt-1">Melewati timeline</div>
                        </div>

                        {{-- Selesai --}}
                        <div class="bg-green-50 rounded-xl p-4 border border-green-100 flex flex-col justify-between">
                            <div class="text-green-600 text-xs font-bold uppercase tracking-wide">Selesai</div>
                            <div class="text-3xl font-black text-green-800 mt-2">{{ $totalKeluar }}</div>
                            <div class="text-[10px] text-green-500 mt-1">Berkas diselesaikan</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- 2. TABEL: SEDANG DIKERJAKAN (Murni Aktif) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="font-bold text-indigo-900 flex items-center gap-2">
                        <i class="fa-solid fa-hourglass-half"></i> Sedang Dikerjakan
                        <span class="bg-white text-indigo-600 text-xs py-0.5 px-2.5 rounded-full shadow-sm ml-2">{{ $daftarBerkas->count() }}</span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berkas</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis & Hak</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status Berkas</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Pembayaran</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Timeline / Durasi</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($daftarBerkas as $berkas)
                                @php
                                    // 1. Ambil Timeline & Waktu Mulai
                                    $timeline = $berkas->jenisPermohonan->waktu_timeline_hari ?? 0;
                                    $waktuMulai = $berkas->waktu_mulai_proses ? \Carbon\Carbon::parse($berkas->waktu_mulai_proses) : $berkas->created_at;
                                    $deadline = $waktuMulai->copy()->addDays($timeline);
                                    
                                    // 2. Karena ini tabel aktif, pembanding selalu NOW()
                                    $sisaHari = now()->diffInDays($deadline, false); 
                                    $isJatuhTempo = $sisaHari < 0;
                                    
                                    // Styling Baris
                                    $rowClass = $isJatuhTempo ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50';
                                @endphp

                                <tr class="{{ $rowClass }} transition-colors">
                                    {{-- Kolom 1: Detail Berkas --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                <i class="fa-solid fa-file-lines text-gray-400 text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $berkas->nomer_berkas }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $berkas->nama_pemohon }}</div>
                                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $berkas->desa }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Jenis Hak --}}
                                    <td class="px-6 py-4">
                                        <span class="block text-sm text-gray-700 font-medium">
                                            {{ optional($berkas->jenisPermohonan)->nama_permohonan ?? '-' }}
                                        </span>
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded border border-gray-200 font-mono">
                                            {{ $berkas->nomer_hak }}
                                        </span>
                                    </td>

                                    {{-- Kolom 3: Status Badge --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($berkas->status == 'Diproses')
                                            <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fa-solid fa-spinner fa-spin mr-1.5 mt-0.5"></i> Proses
                                            </span>
                                        @elseif($berkas->status == 'Pending')
                                            <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200">
                                                <i class="fa-solid fa-pause mr-1.5 mt-0.5"></i> Pending
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-bold rounded-full bg-gray-100 text-gray-800">
                                                {{ $berkas->status }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 4: Status Pembayaran --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($berkas->tgl_bayar)
                                            <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-bold rounded bg-green-100 text-green-700 border border-green-200">
                                                <i class="fa-solid fa-check-circle mr-1 mt-0.5"></i> Lunas
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-bold rounded bg-red-100 text-red-700 border border-red-200">
                                                <i class="fa-solid fa-clock mr-1 mt-0.5"></i> Belum Dibayar
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 5: Timeline / Sisa Waktu --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            @if($isJatuhTempo)
                                                <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded border border-red-200">
                                                    Terlambat {{ abs(intval($sisaHari)) }} Hari
                                                </span>
                                            @else
                                                <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded border border-green-200">
                                                    Sisa {{ intval($sisaHari) }} Hari
                                                </span>
                                            @endif
                                            
                                            {{-- Tampilkan Durasi Berjalan --}}
                                            <span class="text-[10px] text-gray-500 font-mono mt-1">
                                                Berjalan: {{ $berkas->lama_proses_formatted }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Kolom 6: Aksi --}}
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('berkas.show', $berkas->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm inline-flex items-center">
                                            Detail <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic bg-gray-50">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                            <p>Tidak ada berkas yang sedang dikerjakan saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. TABEL: RIWAYAT SELESAI (Menggabungkan 'Dikirim' + 'Selesai di Tempat') --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: false }">
                <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex justify-between items-center cursor-pointer hover:bg-green-100 transition-colors" @click="open = !open">
                    <h3 class="font-bold text-green-900 flex items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> Riwayat Pekerjaan Selesai
                        {{-- Hitung Total Selesai: (History + Di Tempat) --}}
                        <span class="bg-white text-green-600 text-xs py-0.5 px-2.5 rounded-full shadow-sm ml-2">{{ $berkasKeluar->count() + $berkasSelesaiDiTangan->count() }}</span>
                    </h3>
                    <i class="fa-solid fa-chevron-down text-green-600 transition-transform duration-300" :class="{'rotate-180': open}"></i>
                </div>

                <div x-show="open" x-collapse style="display: none;">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pemohon & Hak</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status / Dikirim Ke</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                
                                {{-- A. Tampilkan Berkas Selesai yang masih di tangan --}}
                                @foreach($berkasSelesaiDiTangan as $bs)
                                    <tr class="hover:bg-green-50/50 transition-colors border-l-4 border-green-400">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-700">
                                            {{ $bs->nomer_berkas }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="text-sm text-gray-900">{{ $bs->nama_pemohon }}</div>
                                            <div class="text-xs text-gray-500">{{ $bs->nomer_hak }}</div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-1 rounded">
                                                <i class="fa-solid fa-check mr-1"></i> Selesai (Menunggu Diserahkan)
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right whitespace-nowrap text-sm text-gray-500">
                                            {{ $bs->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- B. Tampilkan Riwayat Kiriman (History Normal) --}}
                                @foreach($berkasKeluar as $histori)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-700">
                                            {{ $histori->berkas->nomer_berkas ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="text-sm text-gray-900">{{ $histori->berkas->nama_pemohon ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $histori->berkas->nomer_hak ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                                                <i class="fa-solid fa-paper-plane mr-1"></i>
                                                {{ optional($histori->keUser)->name ?? 'Dikirim' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right whitespace-nowrap text-sm text-gray-500">
                                            {{ $histori->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach

                                @if($berkasSelesaiDiTangan->isEmpty() && $berkasKeluar->isEmpty())
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                            Belum ada riwayat pekerjaan selesai tahun ini.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>