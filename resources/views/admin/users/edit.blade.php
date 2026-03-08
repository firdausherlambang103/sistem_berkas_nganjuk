<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-user-pen mr-2"></i>
            Edit Pengguna: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="jabatan_id" :value="__('Jabatan')" />
                        <select id="jabatan_id" name="jabatan_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            @foreach ($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ old('jabatan_id', $user->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama_jabatan }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jabatan_id')" class="mt-2" />
                    </div>

                    <div class="mt-6 p-5 border border-gray-200 rounded-md bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Hak Akses Menu & Fitur (Untuk Non-Admin)</h3>
                        <p class="text-sm text-gray-500 mb-4">Centang menu di bawah ini untuk memberikan izin akses kepada user. (Abaikan jika user ini menjabat sebagai Admin).</p>
                        
                        @php 
                            // Pastikan data bisa dibaca baik sebagai array langsung maupun JSON string
                            $akses = is_array($user->akses_menu) ? $user->akses_menu : json_decode($user->akses_menu, true) ?? []; 
                        @endphp
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="akses_menu[]" value="laporan_rinci" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('laporan_rinci', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Laporan Rinci</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="akses_menu[]" value="ruang_kerja" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('ruang_kerja', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Ruang Kerja Internal</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="akses_menu[]" value="silabus" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('silabus', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Silabus (Buku Tanah)</span>
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="akses_menu[]" value="penjadwalan_ukur" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('penjadwalan_ukur', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Penjadwalan Ukur</span>
                            </label>

                            {{-- Checkbox Hak Akses Membuat Berkas Baru --}}
                            <label class="inline-flex items-center bg-indigo-50 p-2 rounded border border-indigo-100 mt-2 sm:mt-0">
                                <input type="checkbox" name="akses_menu[]" value="buat_berkas" class="rounded border-indigo-500 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('buat_berkas', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-bold text-indigo-700">Fitur: Buat Berkas Baru</span>
                            </label>

                            {{-- [BARU] Checkbox Hak Akses WebGIS --}}
                            <label class="inline-flex items-center mt-2 sm:mt-0">
                                <input type="checkbox" name="akses_menu[]" value="WebGIS" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('WebGIS', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 font-semibold">WebGIS (Melihat Peta)</span>
                            </label>

                            {{-- [BARU] Checkbox Hak Akses Kelola Layer Peta --}}
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="akses_menu[]" value="Kelola Layer" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ in_array('Kelola Layer', $akses) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Kelola Layer (Import SHP & Warna)</span>
                            </label>
                        </div>
                    </div>
                    
                    <hr class="my-6 border-gray-200">
                    <p class="text-sm text-gray-600 mb-4">Kosongkan bagian password di bawah ini jika Anda <b>tidak ingin mengubahnya</b>.</p>

                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password Baru (Opsional)')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Perubahan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>