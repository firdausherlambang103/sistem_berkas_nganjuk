<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit / Kembalikan Buku Tanah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                
                <form method="POST" action="{{ route('peminjaman-bt.update', $item->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-input-label for="nomor_berkas" :value="__('Nomor Berkas')" />
                        <x-text-input id="nomor_berkas" class="block mt-1 w-full bg-gray-100 text-gray-500 cursor-not-allowed" 
                            type="text" name="nomor_berkas" 
                            :value="old('nomor_berkas', $item->nomor_berkas ?? $item->nomer_berkas ?? '-')" 
                            readonly />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <x-input-label for="jenis_hak" :value="__('Jenis Hak')" />
                            <select id="jenis_hak" name="jenis_hak" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach(['HM', 'HGB', 'HP', 'HGU', 'Wakaf', 'HPL'] as $hak)
                                    <option value="{{ $hak }}" {{ $item->jenis_hak == $hak ? 'selected' : '' }}>{{ $hak }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <x-input-label for="nomor_hak" :value="__('Nomor Hak')" />
                            <x-text-input id="nomor_hak" class="block mt-1 w-full" type="text" name="nomor_hak" 
                                :value="$item->nomor_hak ?? $item->nomer_hak" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <x-input-label for="kecamatan_id" :value="__('Kecamatan')" />
                            <select id="kecamatan_id" name="kecamatan_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($kecamatans as $kec)
                                    <option value="{{ $kec->id }}" {{ $item->kecamatan_id == $kec->id ? 'selected' : '' }}>{{ $kec->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <x-input-label for="desa_id" :value="__('Desa')" />
                            <select id="desa_id" name="desa_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($desas as $desa)
                                    <option value="{{ $desa->id }}" data-kecamatan="{{ $desa->kecamatan_id }}"
                                        {{ $item->desa_id == $desa->id ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <x-input-label for="status" :value="__('Status Saat Ini')" class="text-yellow-800 font-bold mb-1" />
                        <select id="status" name="status" class="block mt-1 w-full border-yellow-400 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 font-bold text-gray-800">
                            <option value="Ditemukan" {{ $item->status == 'Ditemukan' ? 'selected' : '' }}>Ditemukan</option>
                            <option value="Surat Tugas 1" {{ $item->status == 'Surat Tugas 1' ? 'selected' : '' }}>Surat Tugas 1 (Keluar)</option>
                            <option value="Surat Tugas 2" {{ $item->status == 'Surat Tugas 2' ? 'selected' : '' }}>Surat Tugas 2</option>
                            <option value="Buku Tanah Pengganti" {{ $item->status == 'Buku Tanah Pengganti' ? 'selected' : '' }}>Buku Tanah Pengganti</option>
                            <option value="Blokir" {{ $item->status == 'Blokir' ? 'selected' : '' }}>Blokir</option>
                            <option value="Warkah" {{ $item->status == 'Warkah' ? 'selected' : '' }}>Sedang di Warkah</option>
                            
                            <option disabled>──────────</option>
                            <option value="Dikembalikan" class="bg-green-100 text-green-800">✓ DIKEMBALIKAN (Selesai)</option>
                        </select>
                        <p class="text-xs text-yellow-700 mt-2">
                            Pilih <b>"DIKEMBALIKAN"</b> jika buku tanah sudah kembali ke ruang arsip. Data akan pindah ke menu Riwayat.
                        </p>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="catatan" :value="__('Catatan')" />
                        <textarea id="catatan" name="catatan" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm h-24">{{ $item->catatan }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('peminjaman-bt.index') }}" class="text-gray-600 hover:text-gray-900 mr-4 font-medium text-sm">Batal</a>
                        <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                            {{ __('Simpan Perubahan') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Script Filter Desa (Sama seperti halaman create)
        document.addEventListener('DOMContentLoaded', function() {
            const selectKecamatan = document.getElementById('kecamatan_id');
            const selectDesa = document.getElementById('desa_id');
            const originalOptions = Array.from(selectDesa.options);

            selectKecamatan.addEventListener('change', function() {
                const kecId = this.value;
                const currentVal = selectDesa.value;
                selectDesa.innerHTML = '';
                originalOptions.forEach(opt => {
                    if(opt.dataset.kecamatan == kecId) selectDesa.appendChild(opt);
                });
                if([...selectDesa.options].some(o => o.value == currentVal)) selectDesa.value = currentVal;
            });
        });
    </script>
</x-app-layout>