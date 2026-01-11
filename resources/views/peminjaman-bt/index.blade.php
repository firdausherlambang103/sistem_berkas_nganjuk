<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peminjaman Buku Tanah (Aktif)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                
                <div class="flex border-b border-gray-200 w-full md:w-auto">
                    <a href="{{ route('peminjaman-bt.index') }}" class="px-6 py-2 border-b-2 border-indigo-500 text-indigo-600 font-bold bg-white rounded-t-lg">
                        Sedang Dipinjam
                    </a>
                    <a href="{{ route('peminjaman-bt.riwayat') }}" class="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium hover:bg-gray-50 rounded-t-lg ml-2">
                        Riwayat Pengembalian
                    </a>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <form method="GET" action="{{ route('peminjaman-bt.index') }}" class="flex w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                            placeholder="Cari No Berkas/Hak/Desa...">
                        <button type="submit" class="bg-gray-800 text-white px-3 py-2 rounded-r-md hover:bg-gray-700 transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <a href="{{ route('peminjaman-bt.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm shadow whitespace-nowrap flex items-center">
                        + Pinjam Baru
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3">No. Berkas</th>
                                <th class="px-4 py-3">Buku Tanah</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-bold text-gray-800">{{ $item->nomor_berkas ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $item->jenis_hak }} No. {{ $item->nomor_hak }}</div>
                                    <div class="text-xs text-gray-400">Input: {{ $item->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->desa->nama_desa ?? '-' }}<br>
                                    <span class="text-xs text-gray-400">Kec. {{ $item->kecamatan->nama_kecamatan ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $item->status == 'Ditemukan' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate">{{ $item->catatan }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('peminjaman-bt.edit', $item->id) }}" class="text-white bg-blue-500 hover:bg-blue-600 px-3 py-1.5 rounded text-xs transition">
                                        <i class="fas fa-edit"></i> Edit / Kembali
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Data tidak ditemukan.</td>
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