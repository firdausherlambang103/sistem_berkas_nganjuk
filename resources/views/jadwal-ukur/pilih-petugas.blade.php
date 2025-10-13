<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-user-check mr-2"></i>
            Pilih Petugas Ukur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <p class="text-gray-600">Pilih petugas yang akan ditugaskan untuk membuat jadwal baru. Perhatikan area kerja dan beban kerja saat ini.</p>
            </div>

            @if($semuaPetugas->isEmpty())
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                    <p class="font-bold">Informasi</p>
                    <p>Belum ada data petugas ukur. Silakan tambahkan terlebih dahulu melalui menu Administrasi.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($semuaPetugas as $petugas)
                    <div class="bg-white rounded-lg shadow-md flex flex-col border border-gray-200">
                        <div class="p-6 flex-grow">
                            <!-- Menampilkan Nama & Jabatan -->
                            <h3 class="text-lg font-bold text-gray-900">{{ $petugas->user->name }}</h3>
                            <p class="text-sm text-gray-500 mb-3">{{ optional($petugas->user->jabatan)->nama_jabatan ?? 'Jabatan Belum Diatur' }}</p>
                            
                            <!-- Menampilkan Area Kerja -->
                            <div class="text-xs text-gray-600 h-20 overflow-y-auto border p-2 rounded-md bg-gray-50">
                                <strong class="font-semibold">Area Kerja:</strong><br>
                                <div class="mt-1">
                                    @forelse($petugas->areaKerja as $kecamatan)
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full mb-1">{{ $kecamatan->nama_kecamatan }}</span>
                                    @empty
                                        <span class="italic">Belum diatur</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Menampilkan Beban Kerja -->
                            <div class="flex justify-around text-center my-4 border-t border-b py-3">
                                <div>
                                    {{-- PERUBAHAN: Menggunakan properti baru 'beban_berkas_count' --}}
                                    <h4 class="text-2xl font-bold text-gray-800">{{ $petugas->beban_berkas_count }}</h4>
                                    <small class="text-gray-500">Beban Berkas</small>
                                </div>
                            </div>

                            <!-- Menampilkan Keahlian -->
                            <span class="inline-block bg-gray-200 text-gray-800 px-3 py-1 text-sm font-semibold rounded-full self-start">
                                {{ $petugas->keahlian }}
                            </span>
                        </div>
                        
                        <!-- Tombol Aksi -->
                        <div class="border-t border-gray-200 p-4 text-center bg-gray-50">
                            <a href="{{ route('jadwal-ukur.input-jadwal', ['petugasUkur' => $petugas->id]) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                <i class="fa-solid fa-arrow-right mr-2"></i> Pilih & Buat Jadwal
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
