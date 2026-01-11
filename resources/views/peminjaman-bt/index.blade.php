<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peminjaman Buku Tanah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 flex justify-end">
                <a href="{{ route('peminjaman-bt.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    + Tambah Peminjaman
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">No. Berkas</th>
                                <th class="px-6 py-3">Jenis/No. Hak</th>
                                <th class="px-6 py-3">Desa/Kecamatan</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Catatan</th>
                                <th class="px-6 py-3">Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $item->nomor_berkas ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->jenis_hak }} - {{ $item->nomor_hak }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->desa->nama_desa ?? '-' }} <br>
                                    <span class="text-xs text-gray-400">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full 
                                        {{ $item->status == 'Ditemukan' ? 'text-green-700 bg-green-100' : 'text-yellow-700 bg-yellow-100' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->catatan }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ $item->user->name }} <br>
                                    {{ $item->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center">Belum ada data peminjaman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>