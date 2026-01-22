<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <i class="fa-solid fa-sitemap mr-2 text-indigo-600"></i>
            Manajemen Jabatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <div class="md:col-span-5">
                    <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100 sticky top-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold text-gray-800">Tambah Jabatan</h3>
                            <div class="bg-indigo-50 text-indigo-600 p-2 rounded-full">
                                <i class="fa-solid fa-plus text-sm"></i>
                            </div>
                        </div>
                        
                        <form action="{{ route('admin.jabatan.store') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            <div>
                                <x-input-label for="nama_jabatan" value="Nama Jabatan" />
                                <x-text-input id="nama_jabatan" name="nama_jabatan" type="text" class="mt-1 block w-full" placeholder="Contoh: Kepala Seksi" :value="old('nama_jabatan')" required autofocus />
                                <x-input-error :messages="$errors->get('nama_jabatan')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="urutan" value="Nomor Urutan" />
                                <div class="flex items-center space-x-3 mt-1">
                                    <x-text-input id="urutan" name="urutan" type="number" class="block w-20 text-center" :value="old('urutan', 99)" />
                                    <p class="text-xs text-gray-400 leading-tight">
                                        Angka lebih kecil akan muncul di posisi lebih atas pada menu.
                                    </p>
                                </div>
                                <x-input-error :messages="$errors->get('urutan')" class="mt-2" />
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <label for="is_admin" class="inline-flex items-center cursor-pointer select-none">
                                    <input id="is_admin" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_admin" value="1">
                                    <span class="ms-3 text-sm font-medium text-gray-700">Berikan Akses Administrator</span>
                                </label>
                            </div>

                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150 shadow-lg shadow-gray-300">
                                <i class="fa-solid fa-save mr-2"></i> Simpan Data
                            </button>
                        </form>
                    </div>
                </div>

                <div class="md:col-span-7">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Jabatan</h3>
                        <span class="text-xs bg-white border border-gray-200 px-2 py-1 rounded text-gray-500">
                            Total: {{ $jabatans->count() }}
                        </span>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                        <ul class="divide-y divide-gray-100">
                            @forelse ($jabatans as $jabatan)
                                <li class="p-4 hover:bg-gray-50 transition duration-150">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                        
                                        <div class="flex-shrink-0 flex items-center justify-center">
                                            <div class="w-10 h-10 bg-gray-100 text-gray-500 rounded-lg flex items-center justify-center font-mono font-bold text-lg border border-gray-200" title="Urutan Tampilan">
                                                {{ $jabatan->urutan }}
                                            </div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                                    {{ $jabatan->nama_jabatan }}
                                                </h4>
                                                @if($jabatan->is_admin)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">
                                                        Admin
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-400">
                                                {{ $jabatan->is_admin ? 'Memiliki akses penuh sistem.' : 'Akses pengguna standar.' }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-end gap-2 mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                            <a href="{{ route('admin.jabatan.edit', $jabatan) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-lg hover:bg-yellow-100 transition border border-yellow-200">
                                                <i class="fa-solid fa-pen mr-1"></i> Edit
                                            </a>
                                            
                                            <form action="{{ route('admin.jabatan.destroy', $jabatan) }}" method="POST" onsubmit="return confirm('Hapus jabatan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 text-xs font-medium rounded-lg hover:bg-red-100 transition border border-red-200">
                                                    <i class="fa-solid fa-trash mr-1"></i>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </li>
                            @empty
                                <li class="p-8 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-3xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">Belum ada data jabatan.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>