<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-table-list text-indigo-600 mr-2"></i> {{ __('Data Aset Bidang Tanah (Atribut)') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- BAGIAN FILTER --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                <form action="{{ route('map.aset') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
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
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Kelurahan / Desa</label>
                        <select name="desa" id="desa" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer">
                            <option value="">-- Semua Kelurahan / Desa --</option>
                            @if(isset($allDesaList))
                                @foreach($allDesaList as $d)
                                    <option value="{{ $d }}" {{ (isset($filterDesa) && $filterDesa == $d) ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center h-[38px]">
                            <i class="fa-solid fa-filter mr-2"></i> Terapkan Filter
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
                        <div class="overflow-x-auto">
                            <table id="asetTable" class="w-full text-sm text-left border-collapse">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 border border-gray-200 w-10 text-center">No</th>
                                        <th class="px-4 py-3 border border-gray-200">NIB</th>
                                        <th class="px-4 py-3 border border-gray-200">Tipe Hak</th>
                                        <th class="px-4 py-3 border border-gray-200">Luas Area (m²)</th>
                                        <th class="px-4 py-3 border border-gray-200">Penggunaan</th>
                                        <th class="px-4 py-3 border border-gray-200">Kelurahan / Desa</th>
                                        <th class="px-4 py-3 border border-gray-200">Kecamatan</th>
                                        <th class="px-4 py-3 border border-gray-200 text-center w-32">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($features as $index => $row)
                                        <tr class="hover:bg-gray-50 transition border-b border-gray-200">
                                            <td class="px-4 py-2 border border-gray-200 text-center">{{ $index + 1 }}</td>
                                            <td class="px-4 py-2 border border-gray-200 font-semibold text-indigo-600">{{ $row->nib }}</td>
                                            <td class="px-4 py-2 border border-gray-200">
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-[10px] rounded-md font-bold">{{ $row->tipe_hak }}</span>
                                            </td>
                                            <td class="px-4 py-2 border border-gray-200">{{ $row->luas }}</td>
                                            <td class="px-4 py-2 border border-gray-200">{{ $row->penggunaan }}</td>
                                            <td class="px-4 py-2 border border-gray-200">{{ $row->desa }}</td>
                                            <td class="px-4 py-2 border border-gray-200">{{ $row->kecamatan }}</td>
                                            <td class="px-4 py-2 border border-gray-200 text-center">
                                                <div class="flex justify-center items-center gap-1.5">
                                                    <a href="{{ route('map.index') }}" title="Lihat di Peta" class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-600 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-map text-sm"></i>
                                                    </a>
                                                    <button onclick='editAtributAset({{ $row->raw_data }}, {{ $row->id }}, {{ $row->layer_id }})' title="Edit" class="w-8 h-8 flex items-center justify-center bg-yellow-50 text-yellow-600 border border-yellow-200 rounded hover:bg-yellow-500 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-pen text-sm"></i>
                                                    </button>
                                                    <button onclick="deleteAsset({{ $row->id }})" title="Hapus" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 border border-red-200 rounded hover:bg-red-600 hover:text-white transition shadow-sm">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
            /* Styling Custom DataTables Integrasi dengan Tailwind */
            .dataTables_wrapper .dataTables_length select { border-radius: 6px; padding: 2px 24px 2px 8px; border-color: #d1d5db; margin-left: 5px; margin-right: 5px; font-size: 13px; }
            .dataTables_wrapper .dataTables_filter input { border-radius: 6px; border: 1px solid #d1d5db; padding: 4px 8px; margin-left: 8px; font-size: 13px; }
            table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb; }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #4f46e5; color: white !important; border: none; border-radius: 6px; }
        </style>

        <script>
            function bukaModal(id) { document.getElementById(id).classList.remove('hidden'); }
            function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

            $(document).ready(function() {
                $('#asetTable').DataTable({
                    "language": {
                        "search": "Cari Data Cepat:",
                        "lengthMenu": "Tampilkan _MENU_ baris",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        "paginate": { "first": "Awal", "last": "Akhir", "next": "Maju", "previous": "Mundur" },
                        "zeroRecords": "Tidak ada data aset yang cocok dengan pencarian"
                    },
                    "pageLength": 15,
                    "scrollX": true
                });
            });

            // Fungsi Buka Form Edit
            window.editAtributAset = function(raw, id, layerId) {
                document.getElementById('form_asset_id').value = id;
                document.getElementById('form_layer_id').value = layerId || '';
                
                // Ubah keys JSON ke Uppercase untuk kemudahan
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

            // Fungsi Simpan Form Edit
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

            // Fungsi Hapus Aset
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