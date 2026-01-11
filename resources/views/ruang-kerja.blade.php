<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <i class="fa-solid fa-desktop mr-3 text-indigo-600"></i>
                {{ __('Ruang Kerja Saya') }}
            </h2>

            @can('create-berkas')
                <a href="{{ route('berkas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <i class="fa-solid fa-plus mr-2"></i>
                    <span>Berkas Baru</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- 1. BAGIAN BERKAS MASUK (Menunggu Diterima) --}}
            @if($berkasMenunggu->count() > 0 || request('search_masuk'))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="bg-yellow-100 text-yellow-600 p-2 rounded-full mr-3"><i class="fa-solid fa-inbox"></i></span>
                            Berkas Masuk <span class="ml-2 text-sm font-normal text-gray-500">(Perlu Diterima)</span>
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="relative">
                            <input type="text" name="search_masuk" placeholder="Cari..." class="pl-10 pr-4 py-2 border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-full shadow-sm text-sm w-64" value="{{ request('search_masuk') }}">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengirim</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perihal</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasMenunggu as $berkas)
                                    <tr class="hover:bg-yellow-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-800">{{ $berkas->nomer_berkas }}</div>
                                            <div class="text-xs text-gray-500 mt-1"><i class="fa-regular fa-clock mr-1"></i> {{ $berkas->updated_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $berkas->pengirim->name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ optional($berkas->pengirim->jabatan)->nama_jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-blue-800 bg-blue-100 rounded-full">
                                                {{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <form action="{{ route('berkas.terima', $berkas) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded shadow hover:bg-green-700 transition" title="Terima">
                                                        <i class="fa-solid fa-check mr-1.5"></i> Terima
                                                    </button>
                                                </form>
                                                <form action="{{ route('berkas.tolak', $berkas) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded border border-red-200 hover:bg-red-200 transition" title="Tolak">
                                                        <i class="fa-solid fa-xmark mr-1.5"></i> Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Tidak ada berkas masuk.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. BAGIAN BERKAS DI MEJA SAYA (Utama) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                <div class="p-6">
                    {{-- Header & Search --}}
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3"><i class="fa-solid fa-file-signature"></i></span>
                            Berkas di Meja Saya
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="relative">
                            <input type="text" name="search_di_meja" placeholder="Cari No. Berkas / Pemohon..." class="pl-10 pr-4 py-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-full shadow-sm text-sm w-72" value="{{ request('search_di_meja') }}">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                        </form>
                    </div>

                    {{-- Bulk Actions --}}
                    <div class="bg-gray-50 p-3 rounded-lg mb-4 flex flex-col sm:flex-row justify-end items-center gap-2">
                        <form id="bulk-kirim-form" action="{{ route('berkas.kirim') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="berkas_ids" id="berkas-ids-input">
                            <select name="tujuan_user_id" id="tujuan-user-id-select" class="text-sm border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 w-full sm:w-auto" required>
                                <option value="">-- Kirim Ke --</option>
                                @foreach($daftarUserTujuan as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition shadow-sm whitespace-nowrap">
                                <i class="fa-regular fa-paper-plane mr-2"></i> Kirim Terpilih
                            </button>
                        </form>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left w-10"><input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Info Berkas</th>
                                    
                                    {{-- [MODIFIKASI] Header: Kuasa / Pemohon --}}
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kuasa / Pemohon</th>
                                    
                                    {{-- [MODIFIKASI] Header: Hak & Lokasi --}}
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hak & Lokasi</th>
                                    
                                    {{-- [MODIFIKASI] Header: Status BT --}}
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status BT</th>
                                    
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status WA</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasDiMeja as $berkas)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-4"><input type="checkbox" name="berkas_id[]" value="{{ $berkas->id }}" class="berkas-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></td>
                                        
                                        {{-- 1. Info Berkas --}}
                                        <td class="px-4 py-4">
                                            <a href="{{ route('berkas.show', $berkas) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                                {{ $berkas->nomer_berkas }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-1">{{ optional($berkas->jenisPermohonan)->nama_permohonan }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5"><i class="fa-regular fa-calendar mr-1"></i>{{ $berkas->updated_at->format('d/m/Y H:i') }}</div>
                                        </td>

                                        {{-- 2. Kuasa / Pemohon (Tampilkan Kuasa UTAMA jika ada) --}}
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-bold text-gray-900">
                                                {{ $berkas->penerimaKuasa ? $berkas->penerimaKuasa->nama_kuasa : $berkas->nama_pemohon }}
                                            </div>
                                            
                                            {{-- Jika pakai kuasa, tampilkan nama pemohon asli di bawahnya --}}
                                            @if($berkas->penerimaKuasa)
                                                <div class="text-xs text-gray-500 mt-1 flex items-center">
                                                    <i class="fa-solid fa-user-tag mr-1 text-blue-400" title="Pemohon Asli"></i> 
                                                    {{ Str::limit($berkas->nama_pemohon, 20) }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- 3. Hak & Lokasi (Gabungan Alas Hak, No Hak, Desa, Kecamatan) --}}
                                        <td class="px-4 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $berkas->jenis_alas_hak }} 
                                                <span class="font-mono bg-gray-100 px-1 rounded ml-1 text-gray-700">{{ $berkas->nomer_hak }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center mt-1">
                                                <i class="fa-solid fa-location-dot mr-1 text-red-400"></i> 
                                                {{ Str::limit($berkas->desa, 15) }}, {{ Str::limit($berkas->kecamatan, 15) }}
                                            </div>
                                        </td>
                                        
                                        {{-- 4. Status BT (Sinkronisasi dengan Peminjaman) --}}
                                        <td class="px-4 py-4 text-center">
                                            @if($berkas->peminjamanBukuTanah)
                                                {{-- Jika ada data di peminjaman, ambil status REAL-TIME dari sana --}}
                                                @php $statusPinjam = $berkas->peminjamanBukuTanah->status; @endphp
                                                
                                                @if($statusPinjam == 'Ditemukan')
                                                    <span class="px-2 py-1 text-[10px] font-bold text-white bg-blue-500 rounded-full shadow-sm" title="Buku Tanah Ditemukan">
                                                        Ditemukan
                                                    </span>
                                                @elseif($statusPinjam == 'Surat Tugas' || str_contains($statusPinjam, 'Surat Tugas'))
                                                    <span class="px-2 py-1 text-[10px] font-bold text-white bg-indigo-500 rounded-full shadow-sm" title="Surat Tugas">
                                                        Surat Tugas
                                                    </span>
                                                @elseif($statusPinjam == 'Blokir')
                                                    <span class="px-2 py-1 text-[10px] font-bold text-white bg-red-600 rounded-full shadow-sm">
                                                        Blokir
                                                    </span>
                                                @elseif($statusPinjam == 'Dipinjam')
                                                    <span class="px-2 py-1 text-[10px] font-bold text-white bg-orange-500 rounded-full shadow-sm">
                                                        Dipinjam
                                                    </span>
                                                @elseif($statusPinjam == 'Dikembalikan')
                                                    <span class="px-2 py-1 text-[10px] font-bold text-green-700 bg-green-100 rounded-full border border-green-200">
                                                        Dikembalikan
                                                    </span>
                                                @else
                                                    {{-- Status lain dari peminjaman --}}
                                                    <span class="px-2 py-1 text-[10px] font-bold text-gray-700 bg-gray-200 rounded-full">
                                                        {{ $statusPinjam }}
                                                    </span>
                                                @endif

                                            @elseif($berkas->status_buku_tanah == 'Butuh')
                                                {{-- Jika status_buku_tanah 'Butuh' dan BELUM ada di peminjaman (Permintaan Baru) --}}
                                                <span class="px-2 py-1 text-[10px] font-bold text-red-700 bg-red-100 rounded-full border border-red-200 animate-pulse">
                                                    Perlu BT
                                                </span>
                                            @else
                                                {{-- Jika 'Ada' (dibawa pemohon) --}}
                                                <span class="px-2 py-1 text-[10px] font-bold text-green-700 bg-green-100 rounded-full border border-green-200">
                                                    Ada
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{-- BUTTON WA DENGAN BADGE --}}
                                            <div class="relative inline-block group">
                                                <button type="button" 
                                                        onclick="openWaModal('{{ $berkas->id }}', '{{ $berkas->nomer_wa }}', '{{ $berkas->nama_pemohon }}', '{{ $berkas->nomer_berkas }}', '{{ $berkas->status }}', {{ $berkas->waLogs->count() }})"
                                                        class="w-10 h-10 rounded-full flex items-center justify-center bg-green-50 text-green-600 hover:bg-green-500 hover:text-white transition shadow-sm border border-green-200"
                                                        title="Kirim WhatsApp">
                                                    <i class="fa-brands fa-whatsapp text-xl"></i>
                                                </button>
                                                
                                                {{-- Badge Count --}}
                                                @if($berkas->waLogs && $berkas->waLogs->count() > 0)
                                                    <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 ring-2 ring-white text-[10px] font-bold text-white shadow-sm">
                                                        {{ $berkas->waLogs->count() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                {{-- EDIT --}}
                                                @if(in_array(optional(Auth::user()->jabatan)->nama_jabatan, ['Petugas Loket','Petugas Loket Entri', 'Petugas Loket Penyerahan', 'Admin'])) 
                                                    <a href="{{ route('berkas.edit', $berkas->id) }}" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                @endif

                                                {{-- PENDING --}}
                                                <form action="{{ route('berkas.pending', $berkas) }}" method="POST" class="inline" onsubmit="return handleAksiDenganCatatan(this, 'pending');">@csrf
                                                    <button type="submit" class="p-2 text-gray-500 hover:text-orange-500 hover:bg-orange-50 rounded transition" title="Tunda (Pending)">
                                                        <i class="fa-solid fa-clock"></i>
                                                    </button>
                                                </form>

                                                {{-- SELESAI (Khusus Penyerahan) --}}
                                                @if(optional(Auth::user()->jabatan)->nama_jabatan === 'Petugas Loket Penyerahan')
                                                    <form action="{{ route('berkas.selesaikan', $berkas) }}" method="POST" class="inline">@csrf
                                                        <button type="submit" class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded transition" title="Selesaikan">
                                                            <i class="fa-solid fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- TUTUP (Hanya Administrator) --}}
                                                @if(optional(Auth::user()->jabatan)->is_admin)
                                                    <form action="{{ route('berkas.tutup', $berkas) }}" method="POST" class="inline" onsubmit="return handleAksiDenganCatatan(this, 'tutup');">@csrf
                                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition" title="Tutup / Arsip">
                                                            <i class="fa-solid fa-box-archive"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 flex flex-col items-center justify-center">
                                        <i class="fa-regular fa-folder-open text-4xl mb-2"></i>
                                        <span>Meja Anda bersih! Tidak ada berkas yang sedang diproses.</span>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. BAGIAN BERKAS DITUNDA --}}
            @if($berkasDitunda->count() > 0 || request('search_ditunda'))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-400 opacity-80 hover:opacity-100 transition">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-bold text-gray-700 flex items-center">
                            <i class="fa-solid fa-pause text-orange-500 mr-3"></i> Berkas Ditunda (Pending)
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="relative">
                            <input type="text" name="search_ditunda" placeholder="Cari..." class="pl-10 pr-4 py-2 border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-full shadow-sm text-sm w-64" value="{{ request('search_ditunda') }}">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                        </form>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No. Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Ditunda Sejak</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasDitunda as $berkas)
                                    <tr class="hover:bg-orange-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">{{ $berkas->nomer_berkas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $berkas->nama_pemohon }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $berkas->updated_at->diffForHumans() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <form action="{{ route('berkas.aktifkan', $berkas) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded hover:bg-green-200 transition">
                                                    <i class="fa-solid fa-play mr-1"></i> Lanjutkan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada berkas pending.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- MODAL PILIH TEMPLATE WA --}}
    <div id="waModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeWaModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Panel Modal --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                {{-- Header Modal --}}
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-gray-800" id="modal-title">
                        <i class="fa-brands fa-whatsapp text-green-500 mr-2 text-xl"></i> Kirim Pesan WhatsApp
                    </h3>
                    <button type="button" onclick="closeWaModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="px-6 py-6 bg-white">
                    {{-- Info Penerima --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5 flex items-start gap-3">
                        <div class="bg-blue-200 rounded-full p-2 text-blue-700"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <p class="text-xs text-blue-500 font-bold uppercase tracking-wide">Penerima</p>
                            <p class="text-sm font-bold text-gray-800" id="wa-modal-name">Nama Pemohon</p>
                            <p class="text-xs text-gray-600 font-mono" id="wa-modal-phone">08xxxxxxx</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        {{-- Dropdown Template --}}
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="waTemplateSelect" class="block text-sm font-medium text-gray-700">Pilih Template</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Total Dikirim: <span id="wa-modal-count" class="ml-1 font-bold text-indigo-600">0</span>
                                </span>
                            </div>
                            <select id="waTemplateSelect" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm" onchange="updatePreview()">
                                <option value="">-- Pilih Template Pesan --</option>
                            </select>
                        </div>

                        {{-- Preview Textarea --}}
                        <div>
                            <label for="waMessagePreview" class="block text-sm font-medium text-gray-700 mb-2">Pratinjau Pesan</label>
                            <div class="relative">
                                <textarea id="waMessagePreview" rows="6" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-700" readonly></textarea>
                                <div class="absolute bottom-2 right-2 text-xs text-gray-400 italic">Read-only</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" id="btnKirimWA" onclick="sendWhatsapp()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                        <i class="fa-regular fa-paper-plane mr-2"></i> Kirim Pesan
                    </button>
                    <button type="button" onclick="closeWaModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // --- LOGIKA FORM AKSI ---
        function handleAksiDenganCatatan(form, aksi) {
            event.preventDefault(); 
            const pesan = aksi === 'pending' ? 'Masukkan alasan menunda berkas:' : 'Masukkan catatan untuk menutup berkas:';
            const catatan = prompt(pesan);
            if (catatan === null || catatan.trim() === '') {
                if (catatan !== null) { alert('Aksi dibatalkan karena catatan tidak diisi.'); }
                return false; 
            }
            let hiddenInput = form.querySelector('input[name="catatan_aksi"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'catatan_aksi';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = catatan;
            form.submit(); 
        }

        // --- LOGIKA BULK ACTION ---
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const berkasCheckboxes = document.querySelectorAll('.berkas-checkbox');
            const bulkKirimForm = document.getElementById('bulk-kirim-form');
            const berkasIdsInput = document.getElementById('berkas-ids-input');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('click', function () {
                    berkasCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                });
            }
            if (bulkKirimForm) {
                bulkKirimForm.addEventListener('submit', function (e) {
                    const selectedIds = [];
                    berkasCheckboxes.forEach(checkbox => { if (checkbox.checked) { selectedIds.push(checkbox.value); } });
                    if (selectedIds.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih setidaknya satu berkas untuk dikirim.');
                        return;
                    }
                    if (document.getElementById('tujuan-user-id-select').value === '') {
                        e.preventDefault();
                        alert('Silakan pilih tujuan pengiriman.');
                        return;
                    }
                    berkasIdsInput.value = selectedIds.join(',');
                });
            }
        });

        // --- LOGIKA WA TEMPLATE & KIRIM (SERVER SIDE) ---
        let currentWaData = { id: null, phone: '', nama: '', berkas: '', status: '' };
        let templatesData = [];

        function openWaModal(id, phone, nama, berkas, status, count = 0) {
            // Bersihkan nomor HP
            let cleanPhone = phone.replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);

            currentWaData = { id: id, phone: cleanPhone, nama: nama, berkas: berkas, status: status };
            
            // Update UI Modal
            document.getElementById('wa-modal-name').innerText = nama;
            document.getElementById('wa-modal-phone').innerText = cleanPhone || 'Nomor Kosong';
            document.getElementById('wa-modal-count').innerText = count;
            
            // Reset Form
            const select = document.getElementById('waTemplateSelect');
            select.innerHTML = '<option value="">Sedang memuat...</option>';
            document.getElementById('waMessagePreview').value = "";
            const btnKirim = document.getElementById('btnKirimWA');
            btnKirim.disabled = false;
            btnKirim.innerHTML = '<i class="fa-regular fa-paper-plane mr-2"></i> Kirim Pesan';

            // Tampilkan Modal
            document.getElementById('waModal').classList.remove('hidden');

            // API Fetch
            var apiUrl = "{{ url('/api/wa-templates') }}/" + id;

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil template');
                    return response.json();
                })
                .then(data => {
                    templatesData = data;
                    select.innerHTML = '<option value="">-- Pilih Template Pesan --</option>';
                    
                    if(data.length === 0) {
                        select.innerHTML = '<option value="">Tidak ada template aktif</option>';
                    }
                    
                    data.forEach((tpl, index) => {
                        // Tampilkan indikator jika sudah dikirim
                        let labelCount = "";
                        if (tpl.usage_count && tpl.usage_count > 0) {
                            labelCount = ` (✅ ${tpl.usage_count}x)`;
                        }
                        select.innerHTML += `<option value="${index}">${tpl.judul}${labelCount}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    select.innerHTML = '<option value="">Gagal memuat template</option>';
                });
        }

        function closeWaModal() {
            document.getElementById('waModal').classList.add('hidden');
        }

        function updatePreview() {
            const index = document.getElementById('waTemplateSelect').value;
            if (index === "") {
                document.getElementById('waMessagePreview').value = "";
                return;
            }

            let pesan = templatesData[index].pesan;
            // Ganti Placeholder dengan Data Asli
            pesan = pesan.replace(/{nama_pemohon}/g, currentWaData.nama)
                         .replace(/{nomer_berkas}/g, currentWaData.berkas)
                         .replace(/{status}/g, currentWaData.status);

            document.getElementById('waMessagePreview').value = pesan;
        }

        function sendWhatsapp() {
            const index = document.getElementById('waTemplateSelect').value;
            const pesan = document.getElementById('waMessagePreview').value;

            if (index === "" || !pesan) {
                alert('Silakan pilih template terlebih dahulu.');
                return;
            }
            
            if (!currentWaData.phone) {
                alert('Nomor WhatsApp tidak tersedia untuk pemohon ini.');
                return;
            }

            // UI Loading
            const btnKirim = document.getElementById('btnKirimWA');
            const originalText = btnKirim.innerHTML;
            btnKirim.disabled = true;
            btnKirim.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengirim...';

            const templateId = templatesData[index].id;

            // Kirim ke Backend (Laravel) -> Server WA
            fetch("{{ route('whatsapp.send') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    berkas_id: currentWaData.id,
                    template_id: templateId,
                    nomer_tujuan: currentWaData.phone
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Berhasil! Pesan telah dikirim dan dicatat.');
                    closeWaModal();
                    location.reload(); 
                } else {
                    alert('Gagal mengirim: ' + data.message);
                    btnKirim.disabled = false;
                    btnKirim.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem saat menghubungi server.');
                btnKirim.disabled = false;
                btnKirim.innerHTML = originalText;
            });
        }
    </script>
    @endpush
</x-app-layout>