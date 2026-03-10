<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-chart-pie text-indigo-600 mr-2"></i> {{ __('Grafik Statistik Berkas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Baris 1: Grafik Pemasukan Berkas (Bar Chart) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                        Pemasukan Berkas Tahun {{ $tahun }}
                    </h3>
                    <div class="relative h-72 w-full">
                        <canvas id="chartBulanan"></canvas>
                    </div>
                </div>
            </div>

            {{-- Baris 2: Pie Chart & Doughnut Chart --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Status Berkas --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                            Proporsi Status Berkas
                        </h3>
                        <div class="relative h-64 w-full flex justify-center">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Jenis Permohonan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                            Berdasarkan Jenis Permohonan
                        </h3>
                        <div class="relative h-64 w-full flex justify-center">
                            <canvas id="chartJenis"></canvas>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. Setup Chart Bulanan (Bar)
            const ctxBulan = document.getElementById('chartBulanan').getContext('2d');
            new Chart(ctxBulan, {
                type: 'bar',
                data: {
                    labels: @json($bulanLabels),
                    datasets: [{
                        label: 'Jumlah Berkas Masuk',
                        data: @json($dataBerkasBulan),
                        backgroundColor: 'rgba(79, 70, 229, 0.7)', // Indigo 600
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            // 2. Setup Chart Status (Doughnut)
            const ctxStatus = document.getElementById('chartStatus').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: @json($labelStatus),
                    datasets: [{
                        data: @json($dataStatus),
                        backgroundColor: [
                            '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });

            // 3. Setup Chart Jenis Permohonan (Pie)
            const ctxJenis = document.getElementById('chartJenis').getContext('2d');
            new Chart(ctxJenis, {
                type: 'pie',
                data: {
                    labels: @json($labelJenis),
                    datasets: [{
                        data: @json($dataJenis),
                        backgroundColor: [
                            '#06b6d4', '#f97316', '#ec4899', '#84cc16', '#eab308', '#14b8a6'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });

        });
    </script>
    @endpush
</x-app-layout>