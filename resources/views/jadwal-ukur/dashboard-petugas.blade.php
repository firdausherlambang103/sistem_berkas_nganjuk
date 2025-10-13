<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-chart-bar mr-2"></i>
            Dashboard Beban Kerja Petugas Ukur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Jumlah Berkas Aktif per Petugas</h3>
                    <div id="bebanKerjaChart"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                series: [{
                    name: 'Jumlah Berkas',
                    data: {!! $chartData !!}
                }],
                chart: {
                    type: 'bar',
                    height: 800,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '80%',
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top',
                        },
                    },
                },
                dataLabels: {
                    enabled: true,
                    offsetX: 25,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    },
                    formatter: function(val) {
                        return val > 0 ? val : '';
                    }
                },
                stroke: {
                    show: true,
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: {!! $chartLabels !!},
                    title: {
                        text: 'Jumlah Berkas'
                    },
                     labels: {
                        formatter: function (val) {
                            return parseInt(val);
                        }
                    }
                },
                yaxis: {
                    labels: {
                        show: true,
                        // --- PERBAIKAN DI SINI ---
                        align: 'left',      // 1. Ratakan teks ke kiri
                        minWidth: 100,      // 2. Beri lebar minimum
                        maxWidth: 250,      // 3. Batasi lebar maksimum agar tidak terlalu jauh
                        offsetX: -5,         // 4. Dorong sedikit ke kanan agar tidak menempel
                        style: {
                            colors: [],
                            fontSize: '12px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 400,
                            cssClass: 'apexcharts-yaxis-label',
                        },
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    x: {
                        formatter: function (val) {
                            return val;
                        }
                    },
                    y: {
                        formatter: function (val) {
                            return val + " berkas"
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#bebanKerjaChart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>

