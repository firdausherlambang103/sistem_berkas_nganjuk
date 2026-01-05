<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Template WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Pesan Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- KOLOM KIRI: FORM TAMBAH --}}
                <div class="md:col-span-1">
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Buat Template Baru</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Placeholder yang tersedia:<br>
                                <code class="text-red-500">{nama_pemohon}</code>, 
                                <code class="text-red-500">{nomer_berkas}</code>, 
                                <code class="text-red-500">{status}</code>
                            </p>
                        </header>

                        <form method="post" action="{{ route('admin.wa-templates.store') }}" class="mt-6 space-y-6">
                            @csrf
                            
                            {{-- Judul --}}
                            <div>
                                <x-input-label for="judul" :value="__('Judul Template')" />
                                <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full" required placeholder="Misal: Berkas Selesai" />
                                <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                            </div>

                            {{-- Pesan --}}
                            <div>
                                <x-input-label for="pesan" :value="__('Isi Pesan')" />
                                <textarea id="pesan" name="pesan" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Halo {nama_pemohon}, berkas Anda nomor {nomer_berkas} statusnya {status}."></textarea>
                                <x-input-error :messages="$errors->get('pesan')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN: TABEL DAFTAR --}}
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold mb-4">Daftar Template</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Preview Pesan</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($templates as $template)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $template->judul }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-pre-wrap">{{ Str::limit($template->pesan, 80) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('admin.wa-templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus template ini?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada template. Silakan buat di form sebelah kiri.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>