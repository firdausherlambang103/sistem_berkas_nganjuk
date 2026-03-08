<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Layer & Pengaturan Warna Hak') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <p class="mb-4 text-sm text-gray-600">Atur warna default dan warna spesifik untuk tiap jenis hak (HM, HGB, HP, dll) agar poligon pada peta dapat dirender sesuai peruntukannya secara otomatis.</p>
                    
                    <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">Nama Layer</th>
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
                                    <input type="color" id="warna_{{ $layer->id }}" value="{{ $layer->warna ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hm_{{ $layer->id }}" value="{{ $layer->color_hm ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgb_{{ $layer->id }}" value="{{ $layer->color_hgb ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hp_{{ $layer->id }}" value="{{ $layer->color_hp ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="hgu_{{ $layer->id }}" value="{{ $layer->color_hgu ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="color" id="wakaf_{{ $layer->id }}" value="{{ $layer->color_wakaf ?? '#3388ff' }}" class="h-8 w-12 cursor-pointer border-0 p-0 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="simpanWarna({{ $layer->id }})" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-xs shadow-sm">
                                        Simpan
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada layer map. Silakan import SHP terlebih dahulu melalui menu Peta.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Script AJAX Penyimpanan Warna -->
    <script>
        function simpanWarna(id) {
            const warna = document.getElementById('warna_' + id).value;
            const hm = document.getElementById('hm_' + id).value;
            const hgb = document.getElementById('hgb_' + id).value;
            const hp = document.getElementById('hp_' + id).value;
            const hgu = document.getElementById('hgu_' + id).value;
            const wakaf = document.getElementById('wakaf_' + id).value;

            fetch(`/master-layer/${id}/update-warna`, {
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
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if(data.status === 'success') {
                    alert('Pengaturan warna Hak untuk layer ini berhasil diperbarui!');
                } else {
                    alert('Terjadi kesalahan pada server saat memperbarui warna.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memperbarui. Pastikan Anda memiliki akses (Admin/Kelola Layer).');
            });
        }
    </script>
</x-app-layout>