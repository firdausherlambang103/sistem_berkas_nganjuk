<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peminjaman Buku Tanah (Aktif)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. HEADER & NAVIGASI --}}
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                
                {{-- Tabs Navigasi --}}
                <div class="flex border-b border-gray-200 w-full md:w-auto">
                    <a href="{{ route('peminjaman-bt.index') }}" class="px-6 py-2 border-b-2 border-indigo-500 text-indigo-600 font-bold bg-white rounded-t-lg transition">
                        Sedang Dipinjam
                    </a>
                    <a href="{{ route('peminjaman-bt.riwayat') }}" class="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium hover:bg-gray-50 rounded-t-lg ml-2 transition">
                        Riwayat Pengembalian
                    </a>
                </div>

                {{-- Search & Tombol Tambah --}}
                <div class="flex gap-2 w-full md:w-auto">
                    <form method="GET" action="{{ route('peminjaman-bt.index') }}" class="flex w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                            placeholder="Cari No Berkas/Hak/Desa...">
                        <button type="submit" class="bg-gray-800 text-white px-3 py-2 rounded-r-md hover:bg-gray-700 transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <a href="{{ route('peminjaman-bt.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm shadow whitespace-nowrap flex items-center transition">
                        <i class="fas fa-plus mr-2"></i> Pinjam Baru
                    </a>
                </div>
            </div>

            {{-- 2. BAGIAN REQUEST OTOMATIS (BARU) --}}
            @if(isset($requestOtomatis) && $requestOtomatis->count() > 0)
            <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-blue-800 flex items-center">
                        <i class="fas fa-bell mr-2 text-blue-600 animate-pulse"></i> 
                        Permintaan Buku Tanah <span class="text-sm font-normal ml-2 text-blue-600">(Dari Ruang Kerja)</span>
                    </h3>
                    <span class="bg-blue-200 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $requestOtomatis->count() }} Berkas
                    </span>
                </div>
                
                <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-blue-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-blue-800 uppercase">No. Berkas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-blue-800 uppercase">Pemohon</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-blue-800 uppercase">Alas Hak & No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-blue-800 uppercase">Lokasi</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-blue-800 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($requestOtomatis as $req)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-4 py-3 font-semibold text-gray-700">{{ $req->nomer_berkas }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $req->nama_pemohon }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded border border-gray-200">{{ $req->jenis_alas_hak }}</span> 
                                    <span class="ml-1 font-semibold">{{ $req->nomer_hak }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($req->desa, 15) }}, {{ Str::limit($req->kecamatan, 15) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('peminjaman-bt.proses-otomatis', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition shadow-sm" onclick="return confirm('Proses berkas ini menjadi peminjaman?')">
                                            <i class="fas fa-check mr-1.5"></i> Proses
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- 3. TABEL DATA PEMINJAMAN (UTAMA) --}}
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
                            <tr class="bg-white border-b hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-bold text-gray-800">{{ $item->nomor_berkas ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $item->jenis_hak }} No. {{ $item->nomor_hak }}</div>
                                    <div class="text-xs text-gray-400 mt-1"><i class="far fa-calendar-alt mr-1"></i> Input: {{ $item->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-700 font-medium">{{ $item->desa->nama_desa ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">Kec. {{ $item->kecamatan->nama_kecamatan ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border 
                                        {{ $item->status == 'Ditemukan' ? 'bg-green-100 text-green-800 border-green-200' : 
                                           ($item->status == 'Blokir' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200') }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate text-gray-500 italic">{{ $item->catatan }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('peminjaman-bt.edit', $item->id) }}" class="inline-flex items-center text-white bg-blue-500 hover:bg-blue-600 px-3 py-1.5 rounded-md text-xs font-medium transition shadow-sm">
                                        <i class="fas fa-edit mr-1.5"></i> Edit / Kembali
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 flex flex-col items-center justify-center">
                                    <i class="far fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                    <span>Data tidak ditemukan.</span>
                                </td>
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