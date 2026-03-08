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
                                    <select id="tipe_layer" name="tipe_layer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                        <option value="" disabled selected>-- Pilih Tipe --</option>
                                        <option value="Standar">Standar (1 Warna)</option>
                                        <option value="Utama">Layer Utama (Otomatis)</option>
                                    </select>
                                </div>
                                <div class="w-32">
                                    <label for="warna" class="block text-sm font-medium text-gray-700">Warna Default</label>
                                    <input type="color" id="warna" name="warna" value="#3388ff" class="mt-1 block w-full h-[38px] rounded-md border-gray-300 shadow-sm cursor-pointer p-0.5 bg-white" required>
                                </div>
                            </div>
                            
                            {{-- Teks Penjelasan --}}
                            <div class="bg-blue-50 p-3 rounded-md border border-blue-100 text-xs text-blue-800">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li><strong>Layer Standar:</strong> Menggunakan satu warna default untuk semua aset.</li>
                                    <li><strong>Layer Utama:</strong> Akan mewarnai aset secara otomatis berdasarkan tipe Hak (HM, HGB, dll).</li>
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
                                <th class="px-4 py-3 text-center">Default</th>
                                <th class="px-4 py-3 text-center">Hak Milik</th>
                                <th class="px-4 py-3 text-center">HGB</th>
                                <th class="px-4 py-3 text-center">Pakai</th>
                                <th class="px-4 py-3 text-center">HGU</th>
                                <th class="px-4 py-3 text-center">Wakaf</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($layers as $layer)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $layer->nama_layer }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 bg-gray-100 rounded-md text-xs font-semibold text-gray-600 border">{{ $layer->tipe_layer ?? 'Standar' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="warna_{{ $layer->id }}" value="{{ $layer->warna ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hm_{{ $layer->id }}" value="{{ $layer->color_hm ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer == 'Standar' ? 'disabled opacity-50 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgb_{{ $layer->id }}" value="{{ $layer->color_hgb ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer == 'Standar' ? 'disabled opacity-50 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hp_{{ $layer->id }}" value="{{ $layer->color_hp ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer == 'Standar' ? 'disabled opacity-50 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgu_{{ $layer->id }}" value="{{ $layer->color_hgu ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer == 'Standar' ? 'disabled opacity-50 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="wakaf_{{ $layer->id }}" value="{{ $layer->color_wakaf ?? '#3388ff' }}" class="h-8 w-10 cursor-pointer border-0 p-0 rounded shadow-sm" {{ $layer->tipe_layer == 'Standar' ? 'disabled opacity-50 cursor-not-allowed' : '' }}>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="simpanWarna({{ $layer->id }})" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-xs shadow-sm flex items-center" title="Simpan Pengaturan Warna">
                                            <i class="fa-solid fa-save mr-1"></i> Simpan
                                        </button>
                                        
                                        <form action="{{ route('map.layer.destroy', $layer->id) }}" method="POST" class="inline-block" onsubmit="return confirm('PERINGATAN! Apakah Anda yakin ingin menghapus Layer ini beserta SELURUH aset/poligon di dalamnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-xs shadow-sm flex items-center" title="Hapus Layer">
                                                <i class="fa-solid fa-trash mr-1"></i> Hapus
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

    <script>
        function simpanWarna(id) {
            const warna = document.getElementById('warna_' + id).value;
            const hm = document.getElementById('hm_' + id).value;
            const hgb = document.getElementById('hgb_' + id).value;
            const hp = document.getElementById('hp_' + id).value;
            const hgu = document.getElementById('hgu_' + id).value;
            const wakaf = document.getElementById('wakaf_' + id).value;

            fetch(`{{ url('map/master-layer') }}/${id}/update-warna`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    warna: warna,
                    color_hm: hm,
                    color_hgb: hgb,
                    color_hp: hp,
                    color_hgu: hgu,
                    color_wakaf: wakaf
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Pengaturan warna untuk layer ini berhasil diperbarui!');
                } else {
                    alert('Terjadi kesalahan pada server saat memperbarui warna.');
                }
            });
        }
    </script>
</x-app-layout>