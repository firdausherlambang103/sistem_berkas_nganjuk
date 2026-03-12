<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <a href="{{ route('admin.users.index') }}" class="mr-3 text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            {{ __('Edit Pengguna') }}: <span class="ml-2 text-indigo-600">{{ $user->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-200">
                
                @if ($errors->any())
                    <div class="bg-red-50 p-4 border-b border-red-200">
                        <ul class="list-disc list-inside text-sm text-red-600 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-6 sm:p-8">
                    @csrf
                    @method('PATCH')

                    {{-- INFORMASI DASAR PENGGUNA --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama --}}
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        </div>

                        {{-- Nomor WA --}}
                        <div>
                            <label for="nomer_wa" class="block text-sm font-bold text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" name="nomer_wa" id="nomer_wa" value="{{ old('nomer_wa', $user->nomer_wa) }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="08123456789">
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="jabatan_id" class="block text-sm font-bold text-gray-700 mb-1">Jabatan / Role <span class="text-red-500">*</span></label>
                            <select name="jabatan_id" id="jabatan_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-semibold" required>
                                <option value="" disabled>-- Pilih Jabatan --</option>
                                @foreach(\App\Models\Jabatan::orderBy('nama_jabatan')->get() as $jab)
                                    <option value="{{ $jab->id }}" {{ (old('jabatan_id', $user->jabatan_id) == $jab->id) ? 'selected' : '' }}>
                                        {{ $jab->nama_jabatan }} {{ $jab->is_mitra ? '(Mitra)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Password --}}
                        <div class="md:col-span-2">
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password Baru</label>
                            <input type="password" name="password" id="password" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50" placeholder="Biarkan kosong jika tidak diubah">
                            <p class="text-[10px] text-gray-500 mt-1 italic">*Isi hanya jika Anda ingin mereset password pengguna ini.</p>
                        </div>
                    </div>

                    <hr class="my-8 border-gray-200">

                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-2 text-indigo-600"></i> Pengaturan Hak Akses
                    </h3>

                    {{-- 1. AKSES MENU (SUB-MENU) --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-xl border border-gray-200 shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-3"><i class="fa-solid fa-bars mr-1.5 text-gray-400"></i> Hak Akses Menu Aplikasi</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @php
                                $rawMenu = old('akses_menu', $user->akses_menu);
                                $userAksesMenu = is_array($rawMenu) ? $rawMenu : (json_decode($rawMenu, true) ?? []);
                                if(is_string($userAksesMenu)) $userAksesMenu = json_decode($userAksesMenu, true) ?? [];
                                if(!is_array($userAksesMenu)) $userAksesMenu = [];
                                
                                $listMenu = [
                                    'laporan_rinci' => 'Laporan Rinci',
                                    'ruang_kerja' => 'Ruang Kerja',
                                    'kwitansi' => 'Kwitansi',
                                    'peminjaman_bt' => 'Peminjaman BT',
                                    'penjadwalan_ukur' => 'Jadwal Ukur',
                                    'surat_tugas' => 'Surat Tugas',
                                    'buat_berkas' => 'Buat Berkas',
                                    'edit_berkas' => 'Edit Berkas (Akses Penuh)',
                                    'WebGIS' => 'Peta Utama (WebGIS)',
                                    'Data Aset' => 'Data Aset (Tabel)',
                                    'Kelola Layer' => 'Kelola Layer',
                                    'Statistik' => 'Statistik',
                                    'silabus' => 'Silabus'
                                ];
                            @endphp
                            
                            @foreach($listMenu as $valDB => $label)
                            <label class="inline-flex items-center bg-white p-2.5 rounded-lg border border-gray-200 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 cursor-pointer transition">
                                <input type="checkbox" name="akses_menu[]" value="{{ $valDB }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" {{ in_array($valDB, $userAksesMenu) ? 'checked' : '' }}>
                                <span class="ml-2 text-xs font-bold text-gray-700 select-none">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. AKSES LAYER PETA (KHUSUS WEBGIS) --}}
                    <div class="mb-6 p-5 bg-indigo-50 border border-indigo-100 rounded-xl shadow-sm">
                        <label class="block text-sm font-bold text-indigo-800 mb-1">
                            <i class="fa-solid fa-layer-group mr-1.5"></i> Akses Layer Peta (WebGIS)
                        </label>
                        <p class="text-xs text-indigo-600/80 mb-4 font-medium">Beri centang pada layer yang boleh dilihat oleh pengguna/mitra ini di dalam Peta.</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @php
                                $allLayers = \App\Models\MapLayer::orderBy('nama_layer')->get();
                                
                                $rawLayer = old('akses_layer', $user->akses_layer);
                                $userAksesLayer = is_array($rawLayer) ? $rawLayer : (json_decode($rawLayer, true) ?? []);
                                if(is_string($userAksesLayer)) $userAksesLayer = json_decode($userAksesLayer, true) ?? [];
                                if(!is_array($userAksesLayer)) $userAksesLayer = [];

                                $userAksesLayer = array_map('strval', $userAksesLayer);
                            @endphp
                            
                            @foreach($allLayers as $layer)
                            <label class="inline-flex items-center p-2.5 bg-white rounded-lg border border-indigo-200 shadow-sm hover:bg-indigo-100 hover:border-indigo-300 cursor-pointer transition group">
                                <input type="checkbox" name="akses_layer[]" value="{{ $layer->id }}" class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" {{ in_array((string)$layer->id, $userAksesLayer) ? 'checked' : '' }}>
                                <span class="ml-2 text-xs font-bold text-gray-700 truncate select-none group-hover:text-indigo-800" title="{{ $layer->nama_layer }}">{{ Str::limit($layer->nama_layer, 25) }}</span>
                            </label>
                            @endforeach
                            
                            @if($allLayers->isEmpty())
                                <div class="col-span-full text-xs text-gray-500 italic bg-white p-3 rounded-lg border border-dashed border-gray-300 text-center">Belum ada layer peta yang dibuat di Master Layer.</div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. STATUS AKUN --}}
                    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label class="inline-flex items-center cursor-pointer">
                            {{-- Hidden input agar nilai 0 tetap terkirim jika checkbox tidak dicentang --}}
                            <input type="hidden" name="is_approved" value="0">
                            <input type="checkbox" name="is_approved" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-5 h-5" {{ old('is_approved', $user->is_approved) == 1 ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-bold text-gray-800 select-none">Akun Disetujui (Approved) & Aktif</span>
                        </label>
                        <p class="text-[11px] text-gray-500 mt-1 ml-8">Hapus centang untuk menonaktifkan pengguna ini agar tidak bisa melakukan Login.</p>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                        <a href="{{ route('admin.users.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-800 mr-4 transition">Batal</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition flex items-center">
                            <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>