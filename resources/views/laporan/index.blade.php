<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kinerja Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <label for="seksi" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                        Filter berdasarkan Seksi:
                    </label>
                    
                    <select name="seksi" id="seksi" onchange="this.form.submit()" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-full sm:w-64">
                        <option value="">-- Tampilkan Semua --</option>
                        @foreach($listSeksi as $seksi)
                            <option value="{{ $seksi }}" {{ isset($currentSeksi) && $currentSeksi == $seksi ? 'selected' : '' }}>
                                {{ $seksi }}
                            </option>
                        @endforeach
                    </select>

                    @if(request('seksi'))
                        <a href="{{ route('laporan.index') }}" class="text-sm text-red-600 hover:text-red-800 underline">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>
            @foreach($jabatans as $jabatan)
                @if($jabatan->users->isNotEmpty())
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-700 border-b-2 border-indigo-500 mb-4 pb-2">
                        {{ $jabatan->nama_jabatan }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jabatan->users as $user)
                            @php
                                // Hitung Performa Total (Presentase)
                                $masuk = $user->total_masuk;
                                $keluar = $user->total_keluar;
                                $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
                                
                                // Tentukan warna performa total
                                $warnaRing = $persen >= 80 ? 'text-green-500' : ($persen >= 50 ? 'text-yellow-500' : 'text-red-500');
                                
                                // Ambil Produktivitas Harian (yang sudah dihitung di Controller)
                                $harian = $user->produktivitas_harian;
                            @endphp

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200 border border-gray-100 relative">
                                <div class="p-6">
                                    <div class="absolute top-0 right-0 mt-4 mr-4 bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-green-400">
                                        Hari Ini: {{ $harian }}
                                    </div>

                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold mr-3">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800">{{ $user->name }}</h4>
                                                <p class="text-xs text-gray-500">{{ $jabatan->nama_jabatan }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end mb-2">
                                         <a href="{{ route('laporan.berkas_by_user', $user->id) }}" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full hover:bg-indigo-100 transition">
                                            Detail <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>

                                    <hr class="mb-4">

                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-gray-50 p-2 rounded">
                                            <span class="block text-xs text-gray-500 uppercase">Masuk</span>
                                            <span class="block text-lg font-bold text-gray-700">{{ $masuk }}</span>
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded">
                                            <span class="block text-xs text-gray-500 uppercase">Selesai</span>
                                            <span class="block text-lg font-bold text-green-600">{{ $keluar }}</span>
                                        </div>
                                        <div class="bg-gray-50 p-2 rounded">
                                            <span class="block text-xs text-gray-500 uppercase">Pending</span>
                                            <span class="block text-lg font-bold text-red-500">{{ $user->sisa_berkas }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="flex justify-between items-end mb-1">
                                            <span class="text-xs font-semibold text-gray-600">Produktivitas Total</span>
                                            <span class="text-xs font-bold {{ $warnaRing }}">{{ $persen }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $persen >= 80 ? 'bg-green-500' : ($persen >= 50 ? 'bg-yellow-400' : 'bg-red-500') }}" 
                                                 style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

        </div>
    </div>
</x-app-layout>