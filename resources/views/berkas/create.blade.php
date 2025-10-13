<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-file-circle-plus mr-2"></i>
                Tambah Berkas Baru
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('berkas.store') }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Kolom Kiri --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="nomer_berkas" value="Nomer Berkas" />
                                <x-text-input id="nomer_berkas" name="nomer_berkas" type="text" class="mt-1 block w-full" :value="old('nomer_berkas')" required autofocus />
                                <x-input-error :messages="$errors->get('nomer_berkas')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nama_pemohon" value="Nama Pemohon / Kuasa" />
                                <x-text-input id="nama_pemohon" name="nama_pemohon" type="text" class="mt-1 block w-full" :value="old('nama_pemohon')" required />
                                <x-input-error :messages="$errors->get('nama_pemohon')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="jenis_alas_hak" value="Jenis Alas Hak" />
                                <select id="jenis_alas_hak" name="jenis_alas_hak" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Jenis Hak --</option>
                                    <option value="Letter C" {{ old('jenis_alas_hak') == 'Letter C' ? 'selected' : '' }}>Letter C</option>
                                    <option value="SHM" {{ old('jenis_alas_hak') == 'SHM' ? 'selected' : '' }}>SHM (Sertipikat Hak Milik)</option>
                                    <option value="SHGB" {{ old('jenis_alas_hak') == 'SHGB' ? 'selected' : '' }}>SHGB (Sertipikat Hak Guna Bangunan)</option>
                                    <option value="SHGU" {{ old('jenis_alas_hak') == 'SHGU' ? 'selected' : '' }}>SHGU (Sertipikat Hak Guna Usaha)</option>
                                    <option value="SHP" {{ old('jenis_alas_hak') == 'SHP' ? 'selected' : '' }}>SHP (Sertipikat Hak Pakai)</option>
                                    <option value="SHW" {{ old('jenis_alas_hak') == 'SHW' ? 'selected' : '' }}>SHW (Sertipikat Hak Wakaf)</option>
                                    <option value="SK" {{ old('jenis_alas_hak') == 'SK' ? 'selected' : '' }}>SK (SK Pemberian Hak)</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('jenis_alas_hak')" />
                            </div>
                            <div>
                                <x-input-label for="nomer_hak" value="Nomer Hak" />
                                <x-text-input id="nomer_hak" name="nomer_hak" type="text" class="mt-1 block w-full" :value="old('nomer_hak')" required />
                                <x-input-error :messages="$errors->get('nomer_hak')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="jenis_permohonan_id" value="Jenis Permohonan" />
                                <select id="jenis_permohonan_id" name="jenis_permohonan_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Jenis Permohonan --</option>
                                    @foreach ($jenisPermohonans as $permohonan)
                                        <option value="{{ $permohonan->id }}" {{ old('jenis_permohonan_id') == $permohonan->id ? 'selected' : '' }}>
                                            {{ $permohonan->nama_permohonan }}
                                            @if($permohonan->memerlukan_ukur)
                                                (Perlu Ukur)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('jenis_permohonan_id')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Kolom Kanan --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="kecamatan" value="Kecamatan" />
                                <select id="kecamatan" name="kecamatan" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Kecamatan --</option>
                                    @foreach ($kecamatans as $kecamatan)
                                        <option value="{{ $kecamatan->nama_kecamatan }}" data-id="{{ $kecamatan->id }}" {{ old('kecamatan') == $kecamatan->nama_kecamatan ? 'selected' : '' }}>
                                            {{ $kecamatan->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="desa" value="Desa" />
                                <select id="desa" name="desa" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                    <option value="" disabled selected>-- Pilih Kecamatan Terlebih Dahulu --</option>
                                </select>
                                <x-input-error :messages="$errors->get('desa')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nomer_wa" value="Nomer WhatsApp (Opsional)" />
                                <x-text-input id="nomer_wa" name="nomer_wa" type="text" class="mt-1 block w-full" :value="old('nomer_wa')" />
                                <x-input-error :messages="$errors->get('nomer_wa')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-6">
                        <x-input-label for="catatan" value="Catatan (Opsional)" />
                        <textarea id="catatan" name="catatan" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button><i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Simpan Berkas') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kecamatanSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa');
            const oldDesa = "{{ old('desa') }}";

            function loadDesa(selectedOption) {
                if (!selectedOption || !selectedOption.dataset.id) {
                    desaSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kecamatan Terlebih Dahulu --</option>';
                    desaSelect.disabled = true;
                    return;
                }
                
                const kecamatanId = selectedOption.dataset.id;
                desaSelect.innerHTML = '<option value="" disabled selected>Memuat...</option>';
                desaSelect.disabled = true;

                fetch(`/api/get-desa?kecamatan_id=${kecamatanId}`)
                    .then(response => response.json())
                    .then(data => {
                        desaSelect.innerHTML = '<option value="" disabled selected>-- Pilih Desa --</option>';
                        data.forEach(desa => {
                            const option = document.createElement('option');
                            option.value = desa.nama_desa;
                            option.textContent = desa.nama_desa;
                            if (oldDesa === desa.nama_desa) {
                                option.selected = true;
                            }
                            desaSelect.appendChild(option);
                        });
                        desaSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching desa:', error);
                        desaSelect.innerHTML = '<option value="" disabled selected>Gagal memuat desa</option>';
                    });
            }

            kecamatanSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                loadDesa(selectedOption);
            });

            if (kecamatanSelect.value) {
                const selectedOption = kecamatanSelect.options[kecamatanSelect.selectedIndex];
                loadDesa(selectedOption);
            }
        });
    </script>
    @endpush
</x-app-layout>

