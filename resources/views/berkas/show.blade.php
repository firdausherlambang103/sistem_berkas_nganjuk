<x-app-layout>
    {{-- HEADER HALAMAN --}}
    <x-slot name="header">
        <div class="flex items-center">
             <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-gray-600 mr-4">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Berkas: <span class="text-indigo-600">{{ $berkas->nomer_berkas }}</span>
            </h2>
        </div>
    </x-slot>

    {{-- KONTEN UTAMA --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- KOLOM KIRI: DETAIL INFORMASI BERKAS --}}
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Berkas</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Pemohon</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $berkas->nama_pemohon }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Lokasi</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->desa }}, {{ $berkas->kecamatan }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jenis & No. Hak</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->jenis_alas_hak }} - {{ $berkas->nomer_hak }}</dd>
                        </div>
                        
                        {{-- BAGIAN BARU YANG DITAMBAHKAN --}}
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jenis Permohonan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $berkas->jenisPermohonan->nama_permohonan ?? 'N/A' }}</dd>
                        </div>

                         <div>
                            <dt class="text-sm font-medium text-gray-500">Posisi Saat Ini</dt>
                            <dd class="mt-1 text-sm font-semibold text-green-600 flex items-center">
                                <i class="fa-solid fa-user-check mr-2"></i> {{ $berkas->posisiSekarang->name ?? 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- KOLOM KANAN: LINIMASA RIWAYAT --}}
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Linimasa Riwayat</h3>
                    <div class="relative border-l-2 border-gray-200 ml-3">
                        @forelse ($berkas->riwayat->sortBy('created_at') as $item)
                            <div class="mb-8 ml-6">
                                <span class="absolute flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full -left-4 ring-4 ring-white">
                                    @if($loop->first)
                                        <i class="fa-solid fa-file-circle-plus text-blue-600"></i>
                                    @else
                                        <i class="fa-solid fa-arrow-right-long text-blue-600"></i>
                                    @endif
                                </span>
                                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="items-center justify-between sm:flex mb-2">
                                        <time class="mb-1 text-xs font-normal text-gray-500 sm:order-last sm:mb-0">{{ $item->created_at->isoFormat('dddd, D MMMM YYYY - HH:mm') }}</time>
                                        <p class="text-sm font-semibold text-gray-800">
                                            @if($loop->first)
                                                Berkas Dibuat & Diteruskan ke <span class="text-indigo-600">{{ $item->keUser->name ?? 'N/A' }}</span>
                                            @else
                                                Dikirim dari <span class="text-red-600">{{ $item->dariUser->name ?? 'N/A' }}</span> ke <span class="text-indigo-600">{{ $item->keUser->name ?? 'N/A' }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($item->catatan_pengiriman)
                                    <div class="p-3 text-xs italic text-gray-600 bg-gray-50 rounded-lg border border-gray-200">
                                        "{{ $item->catatan_pengiriman }}"
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="ml-6 text-gray-500">
                                Belum ada riwayat pergerakan untuk berkas ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
