<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Placeholder WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.wa-placeholders.store') }}">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="placeholder" :value="__('Kode Placeholder (Contoh: {nama_pemohon})')" />
                            <x-text-input id="placeholder" class="block mt-1 w-full" type="text" name="placeholder" required />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="deskripsi" :value="__('Mapping Database (Contoh: jenisPermohonan.nama_jenis)')" />
                            <x-text-input id="deskripsi" class="block mt-1 w-full" type="text" name="deskripsi" required />
                            <p class="text-sm text-gray-500 mt-2">
                                <b>Panduan Relasi (Case Sensitive):</b><br>
                                - Jenis Permohonan: <code>jenisPermohonan.nama_jenis</code><br>
                                - Desa: <code>desa.nama_desa</code><br>
                                - Kecamatan: <code>kecamatan.nama_kecamatan</code><br>
                                - Petugas: <code>user.name</code><br>
                                - Petugas Ukur: <code>petugasUkur.nama</code><br>
                                - Penerima Kuasa: <code>penerimaKuasa.nama</code>
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>