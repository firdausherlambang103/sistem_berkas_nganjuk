<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Layer & Pengaturan Warna Hak') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg flex items-center shadow-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg flex items-center shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Form 1: Buat Layer Baru --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900 h-full flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                            <i class="fa-solid fa-plus-circle mr-2 text-indigo-600"></i> 1. Buat Layer Baru
                        </h3>
                        <form action="{{ route('map.layer.store') }}" method="POST" class="flex-1 flex flex-col gap-4">
                            @csrf
                            <div>
                                <label for="nama_layer" class="block text-sm font-medium text-gray-700">Nama Layer</label>
                                <input type="text" id="nama_layer" name="nama_layer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Contoh: Bidang Tanah 2026" required>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <label for="tipe_layer" class="block text-sm font-medium text-gray-700">Tipe Layer</label>
                                    <select id="tipe_layer" name="tipe_layer" onchange="toggleLayerOptions()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                        <option value="" disabled selected>-- Pilih Tipe --</option>
                                        <option value="Standar">Layar Standar (1 Warna Default)</option>
                                        <option value="Utama">Layar Utama (Warna Otomatis per Hak)</option>
                                        <option value="Khusus">Layar Khusus (Custom Atribut Warna)</option>
                                    </select>
                                </div>
                                <div class="w-32" id="div_warna_default">
                                    <label for="warna" class="block text-sm font-medium text-gray-700">Warna Default</label>
                                    <input type="color" id="warna" name="warna" value="#3388ff" class="mt-1 block w-full h-[38px] rounded-md border-gray-300 shadow-sm cursor-pointer p-0.5 bg-white" required>
                                </div>
                            </div>

                            {{-- Wadah untuk Konfigurasi Layar Khusus --}}
                            <div id="opsi_khusus" class="hidden mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Konfigurasi Warna Khusus</label>
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-600">Nama Header Atribut (Ex: TIPE_PENGGUNAAN)</label>
                                    <input type="text" name="khusus_header" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Contoh: penggunaan">
                                </div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Daftar Value & Warna</label>
                                <div id="khusus_container" class="space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text" name="khusus_keys[]" class="flex-1 rounded-md border-gray-300 text-sm" placeholder="Value (Ex: Sawah)">
                                        <input type="color" name="khusus_vals[]" value="#ff0000" class="w-12 h-[38px] rounded-md border-gray-300 p-0.5">
                                    </div>
                                </div>
                                <button type="button" onclick="addKhususRow()" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    <i class="fa-solid fa-plus mr-1"></i> Tambah Baris Warna
                                </button>
                            </div>
                            
                            {{-- Teks Penjelasan --}}
                            <div class="bg-blue-50 p-3 rounded-md border border-blue-100 text-xs text-blue-800">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li><strong>Layer Standar:</strong> Menggunakan satu warna default untuk semua aset.</li>
                                    <li><strong>Layer Utama:</strong> Menggunakan warna yang diatur pada tabel Hak di bawah (HM, HGB, dll).</li>
                                    <li><strong>Layer Khusus:</strong> Menggunakan warna berdasarkan isi teks pada kolom tabel atribut SHP tertentu.</li>
                                </ul>
                            </div>

                            <div class="mt-auto pt-2">
                                <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 transition ease-in-out duration-150 h-[38px]">
                                    <i class="fa-solid fa-save mr-2"></i> Buat Layer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Form 2: Import SHP --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900 bg-gray-50/50 h-full flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                            <i class="fa-solid fa-upload mr-2 text-green-600"></i> 2. Import Data SHP ke Layer
                        </h3>
                        <form action="{{ route('map.import') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col gap-4">
                            @csrf
                            <div>
                                <label for="layer_id" class="block text-sm font-medium text-gray-700">Pilih Layer Tujuan</label>
                                <select id="layer_id" name="layer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm" required>
                                    <option value="" disabled selected>-- Pilih Layer yang sudah dibuat --</option>
                                    @foreach($layers as $layer)
                                        <option value="{{ $layer->id }}">{{ $layer->nama_layer }} ({{ $layer->tipe_layer ?? 'Standar' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="file_zip" class="block text-sm font-medium text-gray-700">File SHP (.zip)</label>
                                <input type="file" id="file_zip" name="file_zip" accept=".zip" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-300 rounded-md bg-white" required>
                            </div>
                            <div class="mt-auto pt-4">
                                <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 transition ease-in-out duration-150 h-[38px]">
                                    <i class="fa-solid fa-file-import mr-2"></i> Upload & Ekstrak SHP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tabel Daftar Layer --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <p class="mb-4 text-sm text-gray-600">Atur warna default dan spesifik untuk tiap jenis hak (HM, HGB, HP, dll) agar poligon dirender sesuai peruntukannya secara otomatis.</p>
                    
                    <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">Nama Layer</th>
                                <th class="px-4 py-3 text-center">Tipe</th>
                                <th class="px-4 py-3 text-center">Default / Aksi</th>
                                <th class="px-4 py-3 text-center">Hak Milik</th>
                                <th class="px-4 py-3 text-center">HGB</th>
                                <th class="px-4 py-3 text-center">Pakai</th>
                                <th class="px-4 py-3 text-center">HGU</th>
                                <th class="px-4 py-3 text-center">Wakaf</th>
                                <th class="px-4 py-3 text-center">Simpan / Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($layers as $layer)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $layer->nama_layer }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($layer->tipe_layer == 'Utama')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-[11px] font-bold">UTAMA</span>
                                    @elseif($layer->tipe_layer == 'Khusus')
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-md text-[11px] font-bold">KHUSUS</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-md text-[11px] font-bold">STANDAR</span>
                                    @endif
                                </td>
                                
                                {{-- Default Warna atau Tombol Edit Modal Khusus --}}
                                <td class="px-4 py-3 text-center">
                                    @if($layer->tipe_layer == 'Khusus')
                                        <button type="button" onclick="bukaModalKhusus({{ $layer->id }}, '{{ $layer->khusus_header }}', {{ json_encode($layer->khusus_colors ?? '{}') }})" class="px-2 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded text-xs hover:bg-purple-100 font-bold whitespace-nowrap">
                                            <i class="fa-solid fa-palette"></i> Edit Warna
                                        </button>
                                        <input type="hidden" id="warna_{{ $layer->id }}" value="{{ $layer->warna ?? '#3388ff' }}">
                                    @else
                                        <input type="color" id="warna_{{ $layer->id }}" value="{{ $layer->warna ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm">
                                    @endif
                                </td>

                                {{-- Pilihan Warna Tipe Utama --}}
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hm_{{ $layer->id }}" value="{{ $layer->color_hm ?? '#28a745' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer != 'Utama' ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgb_{{ $layer->id }}" value="{{ $layer->color_hgb ?? '#ffc107' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer != 'Utama' ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hp_{{ $layer->id }}" value="{{ $layer->color_hp ?? '#17a2b8' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer != 'Utama' ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgu_{{ $layer->id }}" value="{{ $layer->color_hgu ?? '#fd7e14' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer != 'Utama' ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="wakaf_{{ $layer->id }}" value="{{ $layer->color_wakaf ?? '#6f42c1' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer != 'Utama' ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="simpanWarna({{ $layer->id }})" class="px-2 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-xs shadow-sm flex items-center" title="Simpan Pengaturan Warna">
                                            <i class="fa-solid fa-save"></i>
                                        </button>
                                        
                                        <form action="{{ route('map.layer.destroy', $layer->id) }}" method="POST" class="inline-block" onsubmit="return confirm('PERINGATAN! Apakah Anda yakin ingin menghapus Layer ini beserta SELURUH aset/poligon di dalamnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-xs shadow-sm flex items-center" title="Hapus Layer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-gray-300 block"></i>
                                    Belum ada layer map. Silakan buat layer baru terlebih dahulu.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KHUSUS EDIT --}}
    <div id="modalKhusus" class="fixed inset-0 z-[3000] hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-gray-100">
                <div class="bg-purple-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg"><i class="fa-solid fa-palette mr-2"></i> Pengaturan Warna Khusus</h3>
                    <button onclick="document.getElementById('modalKhusus').classList.add('hidden')" class="hover:text-purple-200 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <div class="p-6">
                    <input type="hidden" id="edit_khusus_id">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Header Properties</label>
                        <input type="text" id="edit_khusus_header" class="w-full text-sm border-gray-300 rounded-md focus:ring-purple-500" placeholder="Contoh: TIPE_PENGGUNAAN">
                    </div>
                    
                    <label class="block text-sm font-bold text-gray-700 mb-2">Daftar Warna Value</label>
                    <div id="edit_khusus_container" class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        </div>
                    <button type="button" onclick="addEditKhususRow('', '#000000')" class="mt-3 text-xs font-bold text-purple-600 hover:text-purple-800 border border-purple-200 px-3 py-1.5 rounded-md bg-purple-50"><i class="fa-solid fa-plus mr-1"></i> Tambah Value</button>

                    <div class="pt-5 border-t flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modalKhusus').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                        <button type="button" onclick="simpanWarnaModalKhusus()" class="px-5 py-2.5 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 transition shadow-sm">
                            <i class="fa-solid fa-save mr-2"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logika Toggle Menu Dropdown Form
        function toggleLayerOptions() {
            const tipe = document.getElementById('tipe_layer').value;
            const divOpsiKhusus = document.getElementById('opsi_khusus');
            
            if (tipe === 'Khusus') {
                divOpsiKhusus.classList.remove('hidden');
            } else {
                divOpsiKhusus.classList.add('hidden');
            }
        }

        // Tambah Baris Form Khusus
        function addKhususRow() {
            const container = document.getElementById('khusus_container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center mt-2';
            div.innerHTML = `
                <input type="text" name="khusus_keys[]" class="flex-1 rounded-md border-gray-300 text-sm" placeholder="Value (Ex: Tambak)">
                <input type="color" name="khusus_vals[]" value="#00ff00" class="w-12 h-[38px] rounded-md border-gray-300 p-0.5">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 ml-1"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(div);
        }

        // --- Logika Untuk Tabel Bawah ---
        
        // Modal Khusus Editor
        function addEditKhususRow(key = '', color = '#000000') {
            const container = document.getElementById('edit_khusus_container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center';
            div.innerHTML = `
                <input type="text" name="edit_khusus_keys[]" value="${key}" class="flex-1 rounded-md border-gray-300 shadow-sm text-sm" placeholder="Value (Ex: Sawah)">
                <input type="color" name="edit_khusus_vals[]" value="${color}" class="w-12 h-[38px] rounded-md border-gray-300 p-0.5">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(div);
        }

        function bukaModalKhusus(id, header, colorsJson) {
            document.getElementById('edit_khusus_id').value = id;
            document.getElementById('edit_khusus_header').value = header || '';
            const container = document.getElementById('edit_khusus_container');
            container.innerHTML = '';
            
            let colors = typeof colorsJson === 'string' ? JSON.parse(colorsJson || '{}') : (colorsJson || {});
            let hasKeys = false;

            for(let key in colors) {
                hasKeys = true;
                addEditKhususRow(key, colors[key]);
            }
            if(!hasKeys) addEditKhususRow();
            
            document.getElementById('modalKhusus').classList.remove('hidden');
        }

        function simpanWarnaModalKhusus() {
            const id = document.getElementById('edit_khusus_id').value;
            const header = document.getElementById('edit_khusus_header').value;
            const keys = document.getElementsByName('edit_khusus_keys[]');
            const vals = document.getElementsByName('edit_khusus_vals[]');
            
            let khusus_colors = {};
            for(let i=0; i<keys.length; i++) {
                if(keys[i].value.trim() !== '') khusus_colors[keys[i].value.trim()] = vals[i].value;
            }

            fetch(`{{ url('map/master-layer') }}/${id}/update-warna`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ khusus_header: header, khusus_colors: khusus_colors, warna: '#000000' })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Warna Khusus berhasil disimpan!');
                    location.reload();
                } else { alert('Gagal menyimpan warna khusus.'); }
            });
        }

        // Simpan Warna Standar / Utama
        function simpanWarna(id) {
            const warnaElement = document.getElementById('warna_' + id);
            const warna = warnaElement ? warnaElement.value : '#3388ff';

            const payload = {
                warna: warna,
            };

            // Tambahkan data Utama jika elemennya tidak disabled
            const hm = document.getElementById('hm_' + id);
            if (hm && !hm.disabled) {
                payload.color_hm = hm.value;
                payload.color_hgb = document.getElementById('hgb_' + id).value;
                payload.color_hp = document.getElementById('hp_' + id).value;
                payload.color_hgu = document.getElementById('hgu_' + id).value;
                payload.color_wakaf = document.getElementById('wakaf_' + id).value;
            }

            fetch(`{{ url('map/master-layer') }}/${id}/update-warna`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Pengaturan warna berhasil diperbarui!');
                } else {
                    alert('Terjadi kesalahan pada server saat memperbarui warna.');
                }
            });
        }

        // On Load
        document.addEventListener("DOMContentLoaded", function() {
            toggleLayerOptions();
        });
    </script>
</x-app-layout>