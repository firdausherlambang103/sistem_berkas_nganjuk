<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Master Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.status.store') }}" method="POST" class="mb-8 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    @csrf
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Tambah Status Baru</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <x-input-label for="nama_status" value="Nama Status" />
                            <x-text-input id="nama_status" name="nama_status" type="text" class="mt-1 block w-full" required placeholder="Contoh: Pengumuman, Sengketa, dll" />
                            <x-input-error :messages="$errors->get('nama_status')" class="mt-2" />
                        </div>
                        <div class="flex items-center h-full pb-2">
                            <label for="butuh_waktu_hari" class="inline-flex items-center cursor-pointer">
                                <input id="butuh_waktu_hari" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="butuh_waktu_hari" value="1">
                                <span class="ms-2 text-sm text-gray-600 font-bold">Butuh input berapa hari?</span>
                            </label>
                        </div>
                        <div class="md:col-span-3 text-right">
                            <x-primary-button>Simpan Data</x-primary-button>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Butuh Waktu Hari</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($statuses as $index => $status)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $status->nama_status }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($status->butuh_waktu_hari)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Ya</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('admin.status.edit', $status->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('admin.status.destroy', $status->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus status ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>