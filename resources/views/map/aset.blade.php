<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-table-list text-blue-600 mr-2"></i> {{ __('Data Aset (Atribut Spasial)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    
                    {{-- HEADER & PEMILIH LAYER --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Detail Atribut Aset</h3>
                            <p class="text-sm text-gray-500">Menampilkan data atribut (tabular) dari hasil file SHP.</p>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <form action="{{ route('map.aset') }}" method="GET" class="flex items-center w-full md:w-auto">
                                <label class="text-sm font-bold text-gray-700 mr-2 shrink-0">Pilih Layer Peta:</label>
                                <select name="layer_id" onchange="this.form.submit()" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 w-full md:w-64 bg-gray-50 cursor-pointer">
                                    <option value="">-- Pilih Layer --</option>
                                    @foreach($layers as $layer)
                                        <option value="{{ $layer->id }}" {{ ($selectedLayer && $selectedLayer->id == $layer->id) ? 'selected' : '' }}>
                                            {{ $layer->nama_layer }} ({{ ucfirst($layer->tipe_layer ?? 'Standar') }})
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            
                            <a href="{{ route('map.index') }}" class="shrink-0 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-md text-sm font-bold border border-indigo-200 hover:bg-indigo-100 transition shadow-sm hidden md:flex items-center">
                                <i class="fa-solid fa-map mr-2"></i> Peta
                            </a>
                        </div>
                    </div>

                    {{-- TABEL DATA ASET (DATATABLES) --}}
                    @if($selectedLayer)
                        <div class="mb-3 text-sm font-bold text-indigo-700 bg-indigo-50 inline-block px-3 py-1 rounded border border-indigo-100">
                            Menampilkan atribut tabel dari layer: {{ $selectedLayer->nama_layer }}
                        </div>
                        
                        <div class="overflow-x-auto mt-2">
                            <table id="asetTable" class="w-full text-sm text-left border-collapse">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 border border-gray-200 w-10 text-center">No</th>
                                        @foreach($columns as $col)
                                            <th class="px-4 py-3 border border-gray-200">{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($features as $index => $row)
                                        <tr class="hover:bg-gray-50 transition border-b border-gray-200">
                                            <td class="px-4 py-2 border border-gray-200 text-center">{{ $index + 1 }}</td>
                                            @foreach($columns as $col)
                                                <td class="px-4 py-2 border border-gray-200 text-gray-700 truncate max-w-xs">{{ $row->$col ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300 mt-4">
                            <i class="fa-solid fa-file-circle-xmark text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500 font-medium">Silakan pilih layer peta untuk menampilkan data aset.</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        
        <style>
            /* Custom Tailwind untuk DataTables */
            .dataTables_wrapper .dataTables_length select { border-radius: 6px; padding: 2px 24px 2px 8px; border-color: #d1d5db; margin-left: 5px; margin-right: 5px; }
            .dataTables_wrapper .dataTables_filter input { border-radius: 6px; border: 1px solid #d1d5db; padding: 4px 8px; margin-left: 8px; }
            table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb; }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: #4f46e5; color: white !important; border: none; border-radius: 6px; }
        </style>

        <script>
            $(document).ready(function() {
                $('#asetTable').DataTable({
                    "language": {
                        "search": "Cari Cepat:",
                        "lengthMenu": "Tampilkan _MENU_ baris",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data aset",
                        "paginate": { "first": "Awal", "last": "Akhir", "next": "Maju", "previous": "Mundur" }
                    },
                    "pageLength": 15,
                    "scrollX": true
                });
            });
        </script>
    @endpush
</x-app-layout>