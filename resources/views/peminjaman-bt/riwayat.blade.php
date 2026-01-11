<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Pengembalian Buku Tanah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                
                <div class="flex border-b border-gray-200 w-full md:w-auto">
                    <a href="{{ route('peminjaman-bt.index') }}" class="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium hover:bg-gray-50 rounded-t-lg">
                        Sedang Dipinjam
                    </a>
                    <a href="{{ route('peminjaman-bt.riwayat') }}" class="px-6 py-2 border-b-2 border-indigo-500 text-indigo-600 font-bold bg-white rounded-t-lg ml-2">
                        Riwayat Pengembalian
                    </a>
                </div>

                <div class="w-full md:w-auto">
                    <form method="GET" action="{{ route('peminjaman-bt.riwayat') }}" class="flex w-full md:w-72">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                            placeholder="Cari Riwayat...">
                        <button type="submit" class="bg-gray-800 text-white px-3 py-2 rounded-r-md hover:bg-gray-700 transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">No. Berkas</th>
                                <th class="px-4 py-3">Buku Tanah</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Tanggal Kembali</th>
                                <th class="px-4 py-3">Petugas</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $item->nomor_berkas ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    {{ $item->jenis_hak }} - {{ $item->nomor_hak }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->desa->nama_desa ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-700">
                                    {{ $item->updated_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->user->name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-full text-xs font-bold">
                                        DIKEMBALIKAN
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">Riwayat tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-4 px-4">
                        {{ $data->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>