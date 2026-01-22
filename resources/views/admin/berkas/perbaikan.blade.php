<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            <i class="fa-solid fa-screwdriver-wrench mr-2 text-indigo-600"></i>
            Perbaikan Posisi Berkas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
                <form action="{{ route('admin.perbaikan.index') }}" method="GET">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-8 md:col-span-9">
                            <x-input-label for="keyword" value="Cari Berkas" />
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                </div>
                                <x-text-input id="keyword" name="keyword" type="text" class="pl-10 block w-full" placeholder="Nomor Berkas / Nama Pemohon..." :value="request('keyword')" autofocus required />
                            </div>
                        </div>

                        <div class="col-span-4 md:col-span-3">
                            <x-input-label for="tahun" value="Tahun" />
                            <x-text-input id="tahun" name="tahun" type="number" class="mt-1 block w-full text-center" placeholder="Yaaa" :value="request('tahun', date('Y'))" />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 font-semibold text-sm">
                            <i class="fa-solid fa-search mr-2"></i> Cari Berkas
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center md:text-left">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Masukkan tahun agar pencarian lebih spesifik.
                    </p>
                </form>
            </div>

            @if(request('keyword'))
                @if($berkas)
                    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-indigo-100 animate-fade-in-down">
                        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-indigo-800">Berkas Ditemukan</h3>
                                <span class="bg-indigo-600 text-white text-xs px-2 py-1 rounded font-mono">
                                    TH. {{ $berkas->tahun }}
                                </span>
                            </div>
                            <span class="bg-white text-indigo-600 text-xs px-2 py-1 rounded border border-indigo-200 font-mono font-bold">
                                {{ $berkas->nomer_berkas }}
                            </span>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <span class="block text-gray-400 text-xs uppercase tracking-wider">Nama Pemohon</span>
                                    <span class="font-bold text-gray-800 text-lg">{{ $berkas->nama_pemohon }}</span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <span class="block text-gray-400 text-xs uppercase tracking-wider">Jenis Kegiatan</span>
                                    <span class="font-semibold text-gray-700">{{ $berkas->jenisPermohonan->nama_jenis ?? '-' }}</span>
                                </div>
                                <div class="md:col-span-2 bg-yellow-50 p-4 rounded-lg border border-yellow-200 relative overflow-hidden">
                                    <div class="absolute right-0 top-0 p-4 opacity-10">
                                        <i class="fa-solid fa-map-pin text-6xl text-yellow-600"></i>
                                    </div>
                                    <span class="block text-yellow-700 text-xs font-bold mb-1 uppercase">Posisi Saat Ini</span>
                                    <div class="flex items-center gap-2 relative z-10">
                                        <div class="w-8 h-8 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-700">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <span class="text-gray-900 font-bold block text-lg leading-tight">
                                                {{ $berkas->posisiSekarang->name ?? 'Tidak Diketahui' }} 
                                            </span>
                                            <span class="text-xs text-yellow-700">
                                                {{ $berkas->posisiSekarang->jabatan->nama_jabatan ?? 'Tanpa Jabatan' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <form action="{{ route('admin.perbaikan.update', $berkas->id) }}" method="POST" class="space-y-5" onsubmit="return confirm('PERINGATAN: Anda akan memindahkan berkas ini secara paksa. Lanjutkan?');">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <x-input-label for="target_user_id" value="Pindahkan Ke Petugas Baru:" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-solid fa-arrow-right-to-bracket text-gray-400"></i>
                                        </div>
                                        <select name="target_user_id" id="target_user_id" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                            <option value="" disabled selected>-- Pilih Petugas --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $user->id == $berkas->posisi_sekarang_user_id ? 'disabled class=bg-gray-100 text-gray-400' : '' }}>
                                                    {{ $user->name }} ({{ $user->jabatan->nama_jabatan ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="catatan" value="Alasan Perubahan (Wajib):" />
                                    <textarea id="catatan" name="catatan" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" rows="2" placeholder="Contoh: Salah kirim tahun, berkas fisik ada di meja Pak Budi..." required></textarea>
                                </div>

                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-300 transform active:scale-95 duration-150">
                                    <i class="fa-solid fa-exchange-alt mr-2"></i> Proses Pemindahan
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center text-red-600 animate-pulse">
                        <i class="fa-regular fa-folder-open text-4xl mb-3 opacity-50"></i>
                        <p class="font-bold text-lg">Berkas tidak ditemukan.</p>
                        <p class="text-sm mt-1">
                            Tidak ada berkas dengan kata kunci "<strong>{{ request('keyword') }}</strong>" 
                            @if(request('tahun')) pada tahun <strong>{{ request('tahun') }}</strong> @endif.
                        </p>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>