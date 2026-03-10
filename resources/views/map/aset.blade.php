<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-table-list text-indigo-600 mr-2"></i> {{ __('Data Aset Bidang Tanah (Atribut)') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- BAGIAN FILTER BERLAPIS --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                <form action="{{ route('map.aset') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Layer Peta</label>
                        <select name="layer_id" id="layer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer">
                            <option value="">-- Pilih Layer --</option>
                            @foreach($layers as $layer)
                                <option value="{{ $layer->id }}" {{ ($selectedLayer && $selectedLayer->id == $layer->id) ? 'selected' : '' }}>
                                    {{ $layer->nama_layer }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sumber Data</label>
                        <select name="sumber" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer">
                            <option value="">-- Semua Sumber --</option>
                            <option value="Import" {{ (isset($filterSumber) && $filterSumber == 'Import') ? 'selected' : '' }}>Hasil Import SHP</option>
                            <option value="Manual" {{ (isset($filterSumber) && $filterSumber == 'Manual') ? 'selected' : '' }}>Hasil Gambar Manual</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Kelurahan / Desa</label>
                        <select name="desa" id="desa" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer">
                            <option value="">-- Semua Desa --</option>
                            @if(isset($allDesaList))
                                @foreach($allDesaList as $d)
                                    <option value="{{ $d }}" {{ (isset($filterDesa) && $filterDesa == $d) ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center h-[38px]">
                            <i class="fa-solid fa-filter mr-2"></i> Terapkan
                        </button>
                        <a href="{{ route('map.aset') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center justify-center h-[38px]">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- BAGIAN TABEL DATA --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6 text-gray-900">
                    
                    @if($selectedLayer)
                        
                        {{-- Notifikasi Paging --}}
                        <div class="mb-4 text-sm text-blue-800 bg-blue-50 p-3 rounded-lg border border-blue-200 flex items-center shadow-sm">
                            <i class="fa-solid fa-bolt text-blue-500 mr-2 text-lg"></i>
                            <div>Menampilkan <b>50 data per halaman</b> untuk kecepatan performa. Gunakan Navigasi Halaman di bawah tabel untuk melihat data lainnya.</div>
                        </div>

                        <div class="overflow-x-auto pb-4">
                            {{-- [PENTING] Class nowrap dan style width 100% untuk mengatasi header & body tidak sejajar --}}
                            <table id="asetTable" class="w-full text-sm text-left nowrap" style="width: 100%;">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 border border-gray-200 w-10 text-center whitespace-nowrap">No</th>
                                        <th class="px-4 py-3 border border-gray-200 text-center whitespace-nowrap">Sumber</th>
                                        <th class="px-4 py-3 border border-gray-200 whitespace-nowrap">NIB</th>
                                        <th class="px-4 py-3 border border-gray-200 whitespace-nowrap">Tipe Hak</th>
                                        <th class="px-4 py-3 border border-gray-200 whitespace-nowrap">Luas (m²)</th>
                                        <th class="px-4 py-3 border border-gray-200 min-w-[150px]">Penggunaan</th>
                                        <th class="px-4 py-3 border border-gray-200 min-w-[150px]">Kelurahan/Desa</th>
                                        <th class="px-4 py-3 border border-gray-200 min-w-[150px]">Kecamatan</th>
                                        <th class="px-4 py-3 border border-gray-200 text-center whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $startNumber = ($paginator->currentPage() - 1) * $paginator->perPage() + 1;
                                    @endphp
                                    @foreach($features as $index => $row)
                                        <tr class="hover:bg-gray-50 transition border-b border-gray-200">
                                            <td class="px-4 py-3 border border-gray-200 text-center align-middle">{{ $startNumber + $index }}</td>
                                            
                                            <td class="px-4 py-3 border border-gray-200 text-center align-middle whitespace-nowrap">
                                                @if($row->sumber == 'Manual')
                                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 text-[10px] rounded-md font-bold border border-purple-200 inline-block"><i class="fa-solid fa-pen-nib mr-1"></i> MANUAL</span>
                                                @else
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-[10px] rounded-md font-bold border border-blue-200 inline-block"><i class="fa-solid fa-file-import mr-1"></i> IMPORT</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 border border-gray-200 font-bold text-indigo-600 align-middle whitespace-nowrap">{{ $row->nib }}</td>
                                            <td class="px-4 py-3 border border-gray-200 align-middle whitespace-nowrap">
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-[10px] rounded-md font-bold border border-gray-200">{{ $row->tipe_hak }}</span>
                                            </td>
                                            <td class="px-4 py-3 border border-gray-200 align-middle whitespace-nowrap font-medium">{{ $row->luas }}</td>
                                            <td class="px-4 py-3 border border-gray-200 align-middle capitalize">{{ strtolower($row->penggunaan) }}</td>
                                            <td class="px-4 py-3 border border-gray-200 align-middle capitalize">{{ strtolower($row->desa) }}</td>
                                            <td class="px-4 py-3 border border-gray-200 align-middle capitalize">{{ strtolower($row->kecamatan) }}</td>
                                            <td class="px-4 py-3 border border-gray-200 text-center align-middle whitespace-nowrap">
                                                <div class="flex justify-center items-center gap-1.5">
                                                    {{-- MENGIRIM PARAMETER ZOOM KE HALAMAN PETA --}}
                                                    <a href="{{ route('map.index') }}?zoom_asset={{ $row->id }}" title="Lihat di Peta" class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-600 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-map text-sm"></i>
                                                    </a>
                                                    <button onclick='editAtributAset({{ $row->raw_data }}, {{ $row->id }}, {{ $row->layer_id }})' title="Edit Atribut" class="w-8 h-8 flex items-center justify-center bg-yellow-50 text-yellow-600 border border-yellow-200 rounded hover:bg-yellow-500 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-pen text-sm"></i>
                                                    </button>
                                                    <button onclick="deleteAsset({{ $row->id }})" title="Hapus Aset" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 border border-red-200 rounded hover:bg-red-600 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- KOMPONEN PAGINATION BAWAAN LARAVEL --}}
                        @if($paginator && $paginator->hasPages())
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                {{ $paginator->links() }}
                            </div>
                        @endif

                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <i class="fa-solid fa-layer-group text-5xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-500 font-medium">Silakan pilih layer peta terlebih dahulu untuk menampilkan data aset.</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ATRIBUT (UNTUK EDIT DATA) --}}
    <div id="modalAtribut" class="fixed inset-0 z-[4000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all border border-gray-100">
                <div class="bg-yellow-500 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg"><i class="fa-solid fa-pen-to-square mr-2"></i> Edit Data Aset</h3>
                    <button type="button" onclick="tutupModal('modalAtribut')" class="hover:text-yellow-100 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form id="formAtribut" class="p-6 space-y-4">
                    <input type="hidden" id="form_asset_id">
                    <input type="hidden" id="form_layer_id">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">NIB</label>
                            <input type="text" id="form_nib" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipe Hak <span class="text-red-500">*</span></label>
                            <select id="form_tipehak" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500" required>
                                <option value="Hak Milik">Hak Milik</option>
                                <option value="Hak Guna Bangunan">Hak Guna Bangunan (HGB)</option>
                                <option value="Hak Pakai">Hak Pakai (HP)</option>
                                <option value="Hak Guna Usaha">Hak Guna Usaha (HGU)</option>
                                <option value="Hak Pengelolaan">Hak Pengelolaan (HPL)</option>
                                <option value="Wakaf">Tanah Wakaf</option>
                                <option value="Lainnya">Lainnya / Tidak Diketahui</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Luas (M²)</label>
                            <input type="number" id="form_luas" step="0.01" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penggunaan</label>
                            <input type="text" id="form_penggunaan" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Desa / Kelurahan</label>
                            <input type="text" id="form_kelurahan" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kecamatan</label>
                            <input type="text" id="form_kecamatan" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan Tambahan</label>
                        <textarea id="form_keterangan" rows="2" class="w-full text-sm border-gray-300 rounded-md focus:ring-yellow-500"></textarea>
                    </div>
                    
                    <div class="pt-4 border-t flex justify-end gap-3 mt-4">
                        <button type="button" onclick="tutupModal('modalAtribut')" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                        <button type="button" id="btnSimpan" onclick="simpanAtributAset()" class="px-5 py-2 bg-yellow-500 text-white rounded-md text-sm font-bold hover:bg-yellow-600 transition shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <style>
            /* 1. Perbaikan Jarak Search Box agar tidak menabrak tabel */
            .dataTables_wrapper .dataTables_filter { 
                margin-bottom: 1.25rem; color: #4b5563; font-weight: 600; font-size: 13px;
            }
            .dataTables_wrapper .dataTables_filter input { 
                border-radius: 6px; border: 1px solid #d1d5db; padding: 4px 12px; margin-left: 8px; font-size: 13px; font-weight: normal; outline: none; transition: all 0.2s;
            }
            .dataTables_wrapper .dataTables_filter input:focus { border-color: #4f46e5; box-shadow: 0 0 0 1px #4f46e5; }

            /* 2. Perbaikan Ikon Panah Sorting di Header Tabel */
            table.dataTable thead .sorting, 
            table.dataTable thead .sorting_asc, 
            table.dataTable thead .sorting_desc {
                background-position: right 8px center !important; 
                padding-right: 28px !important; 
                vertical-align: middle;
            }

            /* 3. PERBAIKAN HEADER & BODY TIDAK SEJAJAR (SCROLLX FIX) */
            .dataTables_scrollHeadInner, .dataTables_scrollHeadInner table.dataTable {
                width: 100% !important;
                box-sizing: border-box !important;
            }
            table.dataTable { width: 100% !important; border-collapse: collapse !important; }
            table.dataTable th, table.dataTable td { box-sizing: border-box !important; }
            table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb !important; }
        </style>

        <script>
            function bukaModal(id) { document.getElementById(id).classList.remove('hidden'); }
            function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

            $(document).ready(function() {
                var table = $('#asetTable').DataTable({
                    "paging": false,       
                    "info": false,         
                    "searching": true,     
                    "autoWidth": false,    // [PENTING] Mencegah DataTables memaksa ukuran width
                    "language": {
                        "search": "Cari Cepat di Halaman Ini:",
                        "zeroRecords": "Tidak ada data yang cocok di halaman ini"
                    },
                    "scrollX": true
                });

                // Memaksa DataTables menyesuaikan ulang lebar kolom setelah kerangka termuat
                setTimeout(function() {
                    table.columns.adjust().draw();
                }, 150);
                
                $(window).on('resize', function () {
                    table.columns.adjust();
                });
            });

            window.editAtributAset = function(raw, id, layerId) {
                document.getElementById('form_asset_id').value = id;
                document.getElementById('form_layer_id').value = layerId || '';
                
                let r = {};
                for(let key in raw) r[key.toUpperCase()] = raw[key];

                document.getElementById('form_nib').value = r['NIB'] || '';
                
                let th = r['TIPEHAK'] || r['HAK'] || r['STATUS'] || 'Lainnya';
                let selectTipe = document.getElementById('form_tipehak');
                let optionExists = Array.from(selectTipe.options).some(o => o.value.toLowerCase() === th.toLowerCase());
                selectTipe.value = optionExists ? Array.from(selectTipe.options).find(o => o.value.toLowerCase() === th.toLowerCase()).value : 'Lainnya';
                
                document.getElementById('form_luas').value = r['LUAS'] || r['LUASTERTUL'] || '';
                document.getElementById('form_penggunaan').value = r['PENGGUNAAN'] || '';
                document.getElementById('form_kelurahan').value = r['KELURAHAN'] || r['DESA'] || '';
                document.getElementById('form_kecamatan').value = r['KECAMATAN'] || '';
                document.getElementById('form_keterangan').value = r['KETERANGAN'] || '';

                bukaModal('modalAtribut');
            }

            window.simpanAtributAset = function() {
                let payload = {
                    layer_id: document.getElementById('form_layer_id').value,
                    nib: document.getElementById('form_nib').value,
                    tipehak: document.getElementById('form_tipehak').value,
                    luas: document.getElementById('form_luas').value,
                    penggunaan: document.getElementById('form_penggunaan').value,
                    kelurahan: document.getElementById('form_kelurahan').value,
                    kecamatan: document.getElementById('form_kecamatan').value,
                    keterangan: document.getElementById('form_keterangan').value,
                    is_attribute_update: true,
                    _method: 'PUT'
                };

                let id = document.getElementById('form_asset_id').value;
                let btn = document.getElementById('btnSimpan');
                let originHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
                btn.disabled = true;

                fetch(`/map/asset/${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                }).then(res => res.json()).then(data => {
                    tutupModal('modalAtribut');
                    Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                }).catch(err => {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data.', 'error');
                }).finally(() => {
                    btn.innerHTML = originHtml;
                    btn.disabled = false;
                });
            }

            window.deleteAsset = function(id) {
                Swal.fire({ 
                    title: 'Hapus Data Aset?', 
                    text: "Aset ini akan dihapus permanen dari sistem!", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#d33', 
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/map/asset/${id}`, { 
                            method: 'POST', 
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-HTTP-Method-Override': 'DELETE' } 
                        }).then(res => res.json()).then(data => { 
                            Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload()); 
                        });
                    }
                });
            };
        </script>
    @endpush
</x-app-layout>