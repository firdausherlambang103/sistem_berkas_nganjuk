<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-chart-pie text-indigo-600 mr-2"></i> {{ __('Statistik Lahan (WebGIS)') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- FORM FILTER KECAMATAN & DESA --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-2">
                <form method="GET" action="{{ route('statistik.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Kecamatan</label>
                        <select name="kecamatan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach($allKecamatan as $kec)
                                <option value="{{ $kec }}" {{ $filterKecamatan == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Desa / Kelurahan</label>
                        <select name="desa" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Semua Desa --</option>
                            @foreach($allDesa as $d)
                                <option value="{{ $d }}" {{ $filterDesa == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center h-[38px]">
                            <i class="fa-solid fa-search mr-2"></i> Tampilkan
                        </button>
                        @if($filterKecamatan || $filterDesa)
                            <a href="{{ route('statistik.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-bold shadow-sm transition flex items-center h-[38px]">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- 4 KARTU RINGKASAN (MIRIP REFERENSI) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between transition hover:shadow-md border-l-4 border-l-emerald-500">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Luas</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">{{ $totalLuas }} <span class="text-sm text-gray-500 font-medium">M²</span></h3>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 text-emerald-600 rounded-full">
                        <i class="fa-solid fa-layer-group text-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between transition hover:shadow-md border-l-4 border-l-blue-500">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Kecamatan</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">{{ $totalKecamatan }}</h3>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full">
                        <i class="fa-solid fa-city text-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between transition hover:shadow-md border-l-4 border-l-orange-500">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Desa</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">{{ $totalDesa }}</h3>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-orange-100 text-orange-600 rounded-full">
                        <i class="fa-solid fa-house-flag text-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between transition hover:shadow-md border-l-4 border-l-purple-500">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Sertipikat</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">{{ $totalSertipikat }}</h3>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full">
                        <i class="fa-solid fa-file-signature text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- GRAFIK BAWAH --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-3 uppercase tracking-wider">
                            Proporsi Luas Berdasarkan Tipe Hak
                        </h3>
                        <div class="relative h-64 w-full flex justify-center">
                            <canvas id="chartHak"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-3 uppercase tracking-wider">
                            Jumlah Sertipikat Berdasarkan Penggunaan
                        </h3>
                        <div class="relative h-64 w-full flex justify-center">
                            <canvas id="chartPenggunaan"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEBARAN DESA (Ditampilkan jika tidak memfilter spesifik ke 1 desa) --}}
            @if(!$filterDesa)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 text-gray-900">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 border-b pb-3 uppercase tracking-wider">
                        Sebaran Sertipikat per Kelurahan / Desa
                    </h3>
                    <div class="relative h-80 w-full">
                        <canvas id="chartDesa"></canvas>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const colorPalette = [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', 
                '#06b6d4', '#f97316', '#ec4899', '#84cc16', '#14b8a6'
            ];

            // 1. Chart Hak (Pie)
            new Chart(document.getElementById('chartHak'), {
                type: 'pie',
                data: {
                    labels: @json($labelHak),
                    datasets: [{
                        data: @json($dataHak),
                        backgroundColor: colorPalette,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'right', labels: { usePointStyle: true, font: {size: 11} } },
                        tooltip: { callbacks: { label: function(ctx) { return ' ' + Number(ctx.raw).toLocaleString('id-ID') + ' m²'; } } }
                    }
                }
            });

            // 2. Chart Penggunaan (Bar)
            new Chart(document.getElementById('chartPenggunaan'), {
                type: 'bar',
                data: {
                    labels: @json($labelPenggunaan),
                    datasets: [{
                        label: 'Jumlah Sertipikat',
                        data: @json($dataPenggunaan),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)', // Blue-500
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { 
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: {size: 10} } }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            // 3. Chart Desa (Bar) - Hanya Render jika element ada
            let chartDesaEl = document.getElementById('chartDesa');
            if (chartDesaEl) {
                new Chart(chartDesaEl, {
                    type: 'bar',
                    data: {
                        labels: @json($labelDesa),
                        datasets: [{
                            label: 'Jumlah Sertipikat',
                            data: @json($dataDesa),
                            backgroundColor: 'rgba(16, 185, 129, 0.8)', // Emerald-500
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: { 
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { ticks: { maxRotation: 90, minRotation: 45, font: {size: 10} } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

        });
    </script>
    @endpush
</x-app-layout>