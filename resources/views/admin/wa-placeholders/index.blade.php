<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Placeholder WA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Sukses --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form Tambah Placeholder --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4">Tambah Placeholder Baru</h3>
                    <form action="{{ route('admin.wa-placeholders.store') }}" method="POST" class="flex flex-col md:flex-row gap-4">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Kode (Cth: {nama})</label>
                            <input type="text" name="code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{...}">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <input type="text" name="description" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Penjelasan...">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Contoh Data</label>
                            <input type="text" name="example" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh isi...">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Daftar Placeholder --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contoh</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($placeholders as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="bg-gray-100 text-red-500 px-2 py-1 rounded font-bold">{{ $item->code }}</code>
                                    </td>
                                    <td class="px-6 py-4">{{ $item->description }}</td>
                                    <td class="px-6 py-4 text-gray-500 italic">{{ $item->example ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('admin.wa-placeholders.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus placeholder ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></button>
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