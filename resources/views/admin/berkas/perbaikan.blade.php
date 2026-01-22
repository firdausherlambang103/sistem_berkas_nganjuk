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
                    <x-input-label for="keyword" value="Cari Berkas" />
                    <div class="mt-1 flex gap-2">
                        <x-text-input id="keyword" name="keyword" type="text" class="block w-full" placeholder="Masukkan Nomor Berkas / Nama Pemohon..." :value="request('keyword')" autofocus />
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Fitur ini digunakan jika berkas salah kirim atau macet di user tertentu.</p>
                </form>
            </div>

            @if(request('keyword'))
                @if($berkas)
                    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-indigo-100">
                        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                            <h3 class="font-bold text-indigo-800">Berkas Ditemukan</h3>
                            <span class="bg-white text-indigo-600 text-xs px-2 py-1 rounded border border-indigo-200 font-mono">{{ $berkas->nomor_berkas ?? 'Tanpa Nomor' }}</span>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="block text-gray-400 text-xs">Nama Pemohon</span>
                                    <span class="font-semibold text-gray-800">{{ $berkas->nama_pemohon }}</span>
                                </div>
                                <div>
                                    <span class="block text-gray-400 text-xs">Jenis Kegiatan</span>
                                    <span class="font-semibold text-gray-800">{{ $berkas->jenisPermohonan->nama_jenis ?? '-' }}</span>
                                </div>
                                <div class="md:col-span-2 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                                    <span class="block text-yellow-600 text-xs font-bold mb-1">POSISI SAAT INI</span>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-user-tag text-yellow-500"></i>
                                        <span class="text-gray-800 font-bold">
                                            {{ $berkas->user->name ?? 'Tidak Diketahui' }} 
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            ({{ $berkas->user->jabatan->nama_jabatan ?? 'Tanpa Jabatan' }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <form action="{{ route('admin.perbaikan.update', $berkas->id) }}" method="POST" class="space-y-4" onsubmit="return confirm('Apakah Anda yakin ingin memindahkan berkas ini secara paksa?');">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <x-input-label for="target_user_id" value="Pindahkan Ke User/Petugas:" />
                                    <select name="target_user_id" id="target_user_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" required>
                                        <option value="" disabled selected>-- Pilih Petugas Baru --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $user->id == $berkas->user_id ? 'disabled class=bg-gray-100 text-gray-400' : '' }}>
                                                {{ $user->name }} - {{ $user->jabatan->nama_jabatan ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="catatan" value="Alasan Perubahan (Wajib Diisi)" />
                                    <x-text-input id="catatan" name="catatan" type="text" class="mt-1 block w-full" placeholder="Contoh: Salah kirim, user sebelumnya cuti..." required />
                                </div>

                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-300">
                                    <i class="fa-solid fa-exchange-alt mr-2"></i> Pindahkan Berkas
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center text-red-600">
                        <i class="fa-regular fa-circle-xmark text-3xl mb-2"></i>
                        <p class="font-bold">Berkas tidak ditemukan.</p>
                        <p class="text-xs mt-1">Pastikan nomor berkas atau nama pemohon benar.</p>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>