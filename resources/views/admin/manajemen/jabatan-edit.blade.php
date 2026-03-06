<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-tags mr-2"></i>
            Edit Jabatan: {{ $jabatan->nama_jabatan }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                <form action="{{ route('admin.jabatan.update', $jabatan) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="nama_jabatan" value="Nama Jabatan" />
                        <x-text-input id="nama_jabatan" name="nama_jabatan" type="text" class="mt-1 block w-full" :value="old('nama_jabatan', $jabatan->nama_jabatan)" required autofocus />
                        <x-input-error :messages="$errors->get('nama_jabatan')" class="mt-2" />
                    </div>
                    
                    <div class="mb-4 mt-4">
                        <x-input-label for="urutan" value="Nomor Urut Tampilan" />
                        <x-text-input id="urutan" name="urutan" type="number" class="mt-1 block w-full" :value="old('urutan', $jabatan->urutan)" required />
                        <x-input-error :messages="$errors->get('urutan')" class="mt-2" />
                    </div>
                    
                    <div class="space-y-4 mt-6 p-4 border rounded-md bg-gray-50">
                        <div class="block">
                            <label for="is_admin" class="inline-flex items-center cursor-pointer select-none">
                                <input id="is_admin" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_admin" value="1" {{ old('is_admin', $jabatan->is_admin) ? 'checked' : '' }}>
                                <span class="ms-3 text-sm font-medium text-gray-700">{{ __('Jadikan sebagai Administrator?') }}</span>
                            </label>
                        </div>

                        <div class="block">
                            <label for="is_mitra" class="inline-flex items-center cursor-pointer select-none">
                                <input id="is_mitra" type="checkbox" class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500" name="is_mitra" value="1" {{ old('is_mitra', $jabatan->is_mitra) ? 'checked' : '' }}>
                                <span class="ms-3 text-sm font-medium text-gray-700">{{ __('Jadikan sebagai Jabatan Khusus Mitra (PPAT/Freelance)?') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.jabatan.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <x-primary-button>
                            Simpan Perubahan
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>