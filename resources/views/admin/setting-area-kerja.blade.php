<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-map-location-dot mr-2"></i>
            Setting Area Kerja Petugas Ukur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="mb-4 text-sm text-gray-600">
                        Pilih kecamatan yang menjadi area kerja untuk setiap petugas di bawah ini. Pengaturan ini akan digunakan untuk merekomendasikan petugas saat membuat jadwal ukur baru.
                    </div>

                    <form action="{{ route('admin.setting-area-kerja.update') }}" method="POST">
                        @csrf
                        <div class="space-y-8">
                            @forelse($semuaPetugas as $petugas)
                                <div>
                                    <label class="block font-medium text-md text-gray-800 border-b pb-2 mb-3">
                                        {{ $petugas->user->name }}
                                        <span class="text-sm text-gray-500">({{ optional($petugas->user->jabatan)->nama_jabatan ?? 'N/A' }})</span>
                                    </label>
                                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach($semuaKecamatan as $kecamatan)
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="area_kerja[{{ $petugas->id }}][]" value="{{ $kecamatan->id }}"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    @if($petugas->areaKerja->contains($kecamatan->id)) checked @endif
                                                >
                                                <span class="ms-2 text-sm text-gray-600">{{ $kecamatan->nama_kecamatan }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-8">
                                    Belum ada data petugas ukur yang ditambahkan di sistem. <br>
                                    Silakan tambahkan terlebih dahulu melalui menu "Manajemen Petugas Ukur".
                                </p>
                            @endforelse
                        </div>

                        @if($semuaPetugas->isNotEmpty())
                            <div class="flex items-center justify-end mt-8 border-t pt-6">
                                <x-primary-button>
                                    <i class="fa-solid fa-save mr-2"></i>
                                    {{ __('Simpan Pengaturan') }}
                                </x-primary-button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

