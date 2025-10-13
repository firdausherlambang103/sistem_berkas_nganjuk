<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ route('jadwal-ukur.pilih-petugas') }}" class="text-gray-400 hover:text-gray-600 mr-4" title="Kembali ke Pilih Petugas">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-calendar-plus mr-2"></i>
                Buat Jadwal Ukur untuk: <span class="text-indigo-600">{{ $petugas->user->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('jadwal-ukur.simpan-jadwal') }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    {{-- Input tersembunyi untuk ID petugas --}}
                    <input type="hidden" name="petugas_ukur_id" value="{{ $petugas->id }}">

                    <div class="space-y-6">
                        {{-- Dropdown untuk memilih Berkas --}}
                        <div>
                            <x-input-label for="berkas_id" value="Pilih Berkas yang Akan Diukur" />
                            <select id="berkas_id" name="berkas_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled selected>-- Pilih dari daftar berkas aktif --</option>
                                @foreach ($berkasAktif as $berkas)
                                    <option value="{{ $berkas->id }}" {{ old('berkas_id') == $berkas->id ? 'selected' : '' }}>
                                        {{ $berkas->nomer_berkas }} - {{ $berkas->nama_pemohon }} ({{ $berkas->desa }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('berkas_id')" class="mt-2" />
                        </div>
                        
                        {{-- Input untuk Nomor Surat Tugas --}}
                        <div>
                            <x-input-label for="no_surat_tugas" value="Nomor Surat Tugas (Opsional)" />
                            <x-text-input id="no_surat_tugas" name="no_surat_tugas" type="text" class="mt-1 block w-full" :value="old('no_surat_tugas')" placeholder="cth: 123/ST/IX/2025"/>
                            <x-input-error :messages="$errors->get('no_surat_tugas')" class="mt-2" />
                        </div>

                        {{-- Input untuk Tanggal Rencana Ukur --}}
                        <div>
                            <x-input-label for="tanggal_rencana_ukur" value="Tanggal Rencana Ukur" />
                            <x-text-input id="tanggal_rencana_ukur" name="tanggal_rencana_ukur" type="date" class="mt-1 block w-full" :value="old('tanggal_rencana_ukur')" required />
                            <x-input-error :messages="$errors->get('tanggal_rencana_ukur')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end mt-8 border-t pt-6">
                        <a href="{{ route('jadwal-ukur.pilih-petugas') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button>
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            {{ __('Simpan Jadwal') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
