<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-receipt text-indigo-600 mr-2"></i> {{ __('Daftar Kwitansi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline"><i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- Header & Pencarian --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="bg-indigo-100 text-indigo-600 p-2 rounded-full mr-3"><i class="fa-solid fa-money-check-dollar"></i></span>
                            Data Pembayaran Berkas
                        </h3>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            {{-- Tombol Tambah Kwitansi Manual --}}
                            <button type="button" onclick="bukaModalManual()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all flex items-center justify-center whitespace-nowrap">
                                <i class="fa-solid fa-plus-circle mr-2"></i> Kwitansi Manual
                            </button>

                            <form action="{{ route('kwitansi.index') }}" method="GET" class="relative w-full sm:w-80">
                                <input type="text" name="search" placeholder="Cari No Berkas / Pemohon..." class="pl-10 pr-8 py-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-full shadow-sm text-sm w-full transition-all" value="{{ request('search') }}">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                                @if(request('search'))
                                    <a href="{{ route('kwitansi.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-red-500 transition" title="Reset Pencarian">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-lg border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Info Berkas</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi / Hak</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tgl Bayar</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status Penyerahan</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($berkas as $index => $item)
                                <tr class="hover:bg-indigo-50/30 transition">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center font-medium">{{ $index + 1 }}</td>
                                    
                                    {{-- Kolom Info Berkas --}}
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-extrabold text-indigo-700">{{ $item->nomer_berkas ?? 'N/A' }}</div>
                                        <div class="text-xs font-semibold text-gray-800 mt-0.5"><i class="fa-solid fa-user text-gray-400 mr-1"></i> {{ $item->nama_pemohon }}</div>
                                        <div class="text-[10px] text-gray-500 mt-1 uppercase tracking-wide px-2 py-0.5 bg-gray-100 rounded inline-block">
                                            {{ optional($item->jenisPermohonan)->nama_permohonan ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Kolom Lokasi --}}
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-semibold text-gray-700">
                                            {{ $item->jenis_alas_hak }} <span class="font-mono bg-blue-50 text-blue-700 px-1 rounded border border-blue-100">{{ $item->nomer_hak }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center">
                                            <i class="fa-solid fa-map-location-dot text-red-400 mr-1.5"></i> {{ $item->desa }}, {{ $item->kecamatan }}
                                        </div>
                                    </td>

                                    {{-- Kolom Tgl Bayar --}}
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-green-700 flex items-center">
                                            <i class="fa-solid fa-money-bill-wave mr-1.5 opacity-70"></i> 
                                            {{ \Carbon\Carbon::parse($item->tgl_bayar)->translatedFormat('d M Y') }}
                                        </div>
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($item->tgl_penyerahan_kwitansi)
                                            <div class="inline-flex flex-col">
                                                <span class="px-2.5 py-1 text-[10px] font-bold text-green-700 bg-green-100 rounded-full border border-green-200 mb-1.5 w-fit shadow-sm flex items-center">
                                                    <i class="fa-solid fa-check-circle mr-1"></i> Diserahkan
                                                </span>
                                                <span class="text-xs text-gray-700"><span class="text-gray-400">Ke:</span> <b class="text-indigo-600">{{ $item->penerima_kwitansi }}</b></span>
                                                <span class="text-xs text-gray-500 mt-0.5"><span class="text-gray-400">Tgl:</span> {{ \Carbon\Carbon::parse($item->tgl_penyerahan_kwitansi)->format('d/m/Y') }}</span>
                                            </div>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold text-red-700 bg-red-100 rounded-full border border-red-200 animate-pulse flex items-center w-fit shadow-sm">
                                                <i class="fa-regular fa-clock mr-1"></i> Menunggu Penyerahan
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            @if(!$item->tgl_penyerahan_kwitansi)
                                                <button onclick="bukaModalSerahkan({{ $item->id }}, '{{ $item->nomer_berkas }}')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-bold shadow-sm transition-all flex items-center justify-center w-full">
                                                    <i class="fa-solid fa-hand-holding-hand mr-1.5"></i> Serahkan
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-xs italic font-semibold"><i class="fa-solid fa-check-double text-green-500 mr-1"></i> Selesai</span>
                                            @endif
                                            
                                            <button onclick="bukaModalEdit({{ $item->id }}, '{{ $item->nomer_berkas }}', '{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('Y-m-d') : '' }}', '{{ addslashes($item->penerima_kwitansi) }}', '{{ $item->tgl_penyerahan_kwitansi ? \Carbon\Carbon::parse($item->tgl_penyerahan_kwitansi)->format('Y-m-d') : '' }}')" class="text-amber-500 hover:text-amber-600 text-xs font-bold transition-all flex items-center justify-center mt-1">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Data
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fa-solid fa-receipt text-4xl mb-3 text-gray-300 block"></i>
                                        @if(request('search'))
                                            Data kwitansi dengan pencarian "<b>{{ request('search') }}</b>" tidak ditemukan.
                                        @else
                                            Belum ada data kwitansi pembayaran.
                                        @endif
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

    {{-- Modal Serahkan Kwitansi --}}
    <div id="modalSerahkan" class="fixed inset-0 z-[60] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100">
            <div class="bg-blue-600 px-5 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-hand-holding-hand mr-2"></i> Penyerahan Kwitansi</h3>
                <button type="button" onclick="tutupModalSerahkan()" class="hover:text-blue-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form id="formSerahkan" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-center gap-3">
                    <div class="bg-blue-200 text-blue-700 p-2 rounded-full"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <p class="text-[10px] text-blue-500 font-extrabold uppercase tracking-wider mb-0.5">No Berkas</p>
                        <p class="text-sm font-bold text-gray-800 m-0 leading-none" id="text-nomer-berkas-kwitansi"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Penerima Kwitansi <span class="text-red-500">*</span></label>
                    <input type="text" name="penerima_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Contoh: Bpk. Budi / Kuasa / Sendiri" required>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Diserahkan <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_penyerahan_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ date('Y-m-d') }}" required>
                </div>
                
                <div class="flex justify-end gap-2 border-t pt-5 mt-2">
                    <button type="button" onclick="tutupModalSerahkan()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-bold text-gray-700 transition">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-bold text-white transition shadow-sm"><i class="fa-solid fa-save mr-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kwitansi --}}
    <div id="modalEdit" class="fixed inset-0 z-[60] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100">
            <div class="bg-amber-500 px-5 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-pen-to-square mr-2"></i> Edit Data Kwitansi</h3>
                <button type="button" onclick="tutupModalEdit()" class="hover:text-amber-100 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form id="formEdit" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="bg-amber-50 p-3 rounded-lg border border-amber-100 flex items-center gap-3">
                    <div class="bg-amber-200 text-amber-700 p-2 rounded-full"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <p class="text-[10px] text-amber-600 font-extrabold uppercase tracking-wider mb-0.5">No Berkas</p>
                        <p class="text-sm font-bold text-gray-800 m-0 leading-none" id="edit-nomer-berkas"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="date" id="edit_tgl_bayar" name="tgl_bayar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Penerima Kwitansi</label>
                    <input type="text" id="edit_penerima_kwitansi" name="penerima_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" placeholder="Opsional jika belum diserahkan">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Diserahkan</label>
                    <input type="date" id="edit_tgl_penyerahan" name="tgl_penyerahan_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                </div>
                
                <div class="flex justify-end gap-2 border-t pt-5 mt-2">
                    <button type="button" onclick="tutupModalEdit()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-bold text-gray-700 transition">Batal</button>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 px-4 py-2 rounded-lg text-sm font-bold text-white transition shadow-sm"><i class="fa-solid fa-save mr-1"></i> Update Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Kwitansi Manual --}}
    <div id="modalManual" class="fixed inset-0 z-[60] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-gray-100 my-8">
            <div class="bg-green-600 px-5 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-plus-circle mr-2"></i> Tambah Kwitansi Manual</h3>
                <button type="button" onclick="tutupModalManual()" class="hover:text-green-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-6">
                {{-- Form Pencarian AJAX --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Cari Nomer Berkas (Belum Lunas) <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="input_cari_nomer" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm uppercase" placeholder="Ketik Nomer Berkas...">
                        <button type="button" id="btn_cari_nomer" onclick="cariBerkasManual()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-bold shadow-sm transition-all whitespace-nowrap">
                            Cari Berkas
                        </button>
                    </div>
                    <p id="error_cari" class="text-xs text-red-600 font-bold mt-2 hidden"></p>
                </div>

                {{-- Hasil Pencarian Berkas (Awalnya Hidden) --}}
                <div id="hasil_cari_berkas" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-5 hidden">
                    <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                        <div class="col-span-2">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Nama Pemohon</span>
                            <div class="text-sm font-bold text-gray-900" id="info_nama"></div>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Jenis Permohonan</span>
                            <div class="text-sm font-semibold text-indigo-700" id="info_jenis"></div>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Jenis & Nomer Hak</span>
                            <div class="text-sm font-semibold text-gray-800" id="info_hak"></div>
                        </div>
                        <div class="col-span-2 border-t pt-2 mt-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Lokasi Desa</span>
                            <div class="text-sm text-gray-700" id="info_lokasi"></div>
                        </div>
                    </div>
                </div>

                {{-- Form Input Data Pembayaran (Awalnya Hidden) --}}
                <form id="form_manual_inputs" method="POST" action="{{ route('kwitansi.store-manual') }}" class="hidden space-y-4 border-t pt-5">
                    @csrf
                    <input type="hidden" name="berkas_id" id="manual_berkas_id">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Bayar (Lunas) <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_bayar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-yellow-50 p-3 rounded-lg border border-yellow-200 mt-4">
                        <div class="col-span-2 text-xs font-bold text-yellow-800">
                            <i class="fa-solid fa-circle-info mr-1"></i> Isi bagian ini jika kwitansi fisik langsung diserahkan hari ini (Opsional):
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Penerima Kwitansi</label>
                            <input type="text" name="penerima_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-xs py-1.5" placeholder="Contoh: Bpk. Budi">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Diserahkan</label>
                            <input type="date" name="tgl_penyerahan_kwitansi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-xs py-1.5">
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" onclick="tutupModalManual()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-bold text-gray-700 transition">Batal</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm font-bold text-white transition shadow-sm flex items-center">
                            <i class="fa-solid fa-save mr-1.5"></i> Simpan Kwitansi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Modal Serahkan
        function bukaModalSerahkan(idBerkas, nomerBerkas) {
            document.getElementById('formSerahkan').action = '/kwitansi/' + idBerkas + '/serahkan';
            document.getElementById('text-nomer-berkas-kwitansi').innerText = nomerBerkas;
            document.getElementById('modalSerahkan').classList.remove('hidden');
        }
        function tutupModalSerahkan() {
            document.getElementById('modalSerahkan').classList.add('hidden');
            document.getElementById('formSerahkan').reset();
        }

        // Modal Edit
        function bukaModalEdit(idBerkas, nomerBerkas, tglBayar, penerima, tglSerah) {
            document.getElementById('formEdit').action = '/kwitansi/' + idBerkas + '/update';
            document.getElementById('edit-nomer-berkas').innerText = nomerBerkas;
            document.getElementById('edit_tgl_bayar').value = tglBayar;
            document.getElementById('edit_penerima_kwitansi').value = penerima || '';
            document.getElementById('edit_tgl_penyerahan').value = tglSerah || '';
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function tutupModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
            document.getElementById('formEdit').reset();
        }

        // =====================================
        // LOGIKA MODAL KWITANSI MANUAL (AJAX)
        // =====================================
        function bukaModalManual() {
            document.getElementById('modalManual').classList.remove('hidden');
        }

        function tutupModalManual() {
            document.getElementById('modalManual').classList.add('hidden');
            document.getElementById('input_cari_nomer').value = '';
            document.getElementById('error_cari').classList.add('hidden');
            document.getElementById('hasil_cari_berkas').classList.add('hidden');
            document.getElementById('form_manual_inputs').classList.add('hidden');
            document.getElementById('form_manual_inputs').reset();
        }

        function cariBerkasManual() {
            const nomer = document.getElementById('input_cari_nomer').value;
            const btn = document.getElementById('btn_cari_nomer');
            const errorMsg = document.getElementById('error_cari');
            const resultDiv = document.getElementById('hasil_cari_berkas');
            const formInputs = document.getElementById('form_manual_inputs');

            if(!nomer) {
                errorMsg.innerText = "Silakan isi Nomer Berkas terlebih dahulu!";
                errorMsg.classList.remove('hidden');
                return;
            }

            // State Loading
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari...';
            btn.disabled = true;
            errorMsg.classList.add('hidden');
            resultDiv.classList.add('hidden');
            formInputs.classList.add('hidden');

            // Fetch AJAX GET
            fetch(`/kwitansi/cari-berkas?nomer_berkas=${encodeURIComponent(nomer)}`)
                .then(res => res.json())
                .then(data => {
                    btn.innerHTML = 'Cari Berkas';
                    btn.disabled = false;

                    if(data.success) {
                        // Isi data ke card informasi
                        document.getElementById('manual_berkas_id').value = data.data.id;
                        document.getElementById('info_nama').innerText = data.data.nama_pemohon;
                        document.getElementById('info_hak').innerText = data.data.jenis_alas_hak + ' - ' + data.data.nomer_hak;
                        document.getElementById('info_lokasi').innerText = data.data.desa + ', ' + data.data.kecamatan;
                        document.getElementById('info_jenis').innerText = data.data.jenis_permohonan;

                        // Tampilkan card dan form submit
                        resultDiv.classList.remove('hidden');
                        formInputs.classList.remove('hidden');
                    } else {
                        // Tampilkan error (Misal: sudah dibayar / tidak ditemukan)
                        errorMsg.innerText = data.message;
                        errorMsg.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    btn.innerHTML = 'Cari Berkas';
                    btn.disabled = false;
                    errorMsg.innerText = 'Terjadi kesalahan pada jaringan/server.';
                    errorMsg.classList.remove('hidden');
                });
        }

        // Deteksi Enter di input pencarian manual
        document.getElementById('input_cari_nomer').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Mencegah form submit default
                cariBerkasManual();
            }
        });
    </script>
    @endpush
</x-app-layout>