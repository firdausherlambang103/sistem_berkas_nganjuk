<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-brands fa-whatsapp mr-2 text-green-500"></i> {{ __('WA Templates') }}
            </h2>
            <a href="{{ route('admin.wa-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none transition">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Template
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Template</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Isi Pesan</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($templates as $item)
                                <tr class="hover:bg-gray-50 transition top-align">
                                    <td class="px-6 py-4 whitespace-nowrap align-top font-bold text-gray-700">
                                        {{ $item->nama }}
                                    </td>
                                    <td class="px-6 py-4 align-top text-sm text-gray-600 whitespace-pre-wrap leading-relaxed max-w-lg">
                                        {{ $item->template }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap align-top">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->status == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                        <a href="{{ route('admin.wa-templates.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fa-regular fa-pen-to-square"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.wa-templates.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus template ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fa-regular fa-trash-can"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">
                                        Belum ada template.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>