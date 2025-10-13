<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-user-pen mr-2"></i>
            Edit Petugas Ukur: {{ $petugasUkur->user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('admin.petugas-ukur.update', $petugasUkur) }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="user_id" value="Pengguna (User)" />
                            <x-text-input id="user_id" name="user_id" type="text" class="mt-1 block w-full bg-gray-100" :value="$petugasUkur->user->name" disabled />
                            <p class="mt-1 text-xs text-gray-500">Nama pengguna tidak dapat diubah.</p>
                        </div>
                        
                        <div>
                            <x-input-label for="keahlian" value="Keahlian" />
                            <x-text-input id="keahlian" name="keahlian" type="text" class="mt-1 block w-full" :value="old('keahlian', $petugasUkur->keahlian)" required placeholder="cth: Pengukuran, Pemetaan, dll."/>
                            <x-input-error :messages="$errors->get('keahlian')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="area_kerja" value="Area Kerja" />
                            <textarea id="area_kerja" name="area_kerja" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Isi dengan kecamatan atau wilayah kerja, pisahkan dengan koma. Contoh: Mojoroto, Pesantren, Kota">{{ old('area_kerja', $petugasUkur->area_kerja) }}</textarea>
                            <x-input-error :messages="$errors->get('area_kerja')" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.petugas-ukur.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button><i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Simpan Perubahan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

