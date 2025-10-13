<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-user-plus mr-2"></i>
            Tambah Petugas Ukur Baru
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('admin.petugas-ukur.store') }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="user_id" value="Pilih Pengguna (User)" />
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled selected>-- Pilih dari daftar user yang belum terdaftar --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="keahlian" value="Keahlian" />
                            <x-text-input id="keahlian" name="keahlian" type="text" class="mt-1 block w-full" :value="old('keahlian')" required placeholder="cth: Pengukuran, Pemetaan, dll."/>
                            <x-input-error :messages="$errors->get('keahlian')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="area_kerja" value="Area Kerja (Contoh)" />
                            <textarea id="area_kerja" name="area_kerja" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Isi dengan kecamatan atau wilayah kerja, pisahkan dengan koma. Contoh: Mojoroto, Pesantren, Kota">{{ old('area_kerja') }}</textarea>
                            <x-input-error :messages="$errors->get('area_kerja')" class="mt-2" />
                        </div>

                    </div>
                    <div class="flex items-center justify-end mt-8">
                        <a href="{{ route('admin.petugas-ukur.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <x-primary-button><i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

