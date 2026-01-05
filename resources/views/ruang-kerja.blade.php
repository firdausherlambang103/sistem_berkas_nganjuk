<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fa-solid fa-desktop mr-2"></i>
                {{ __('Ruang Kerja Saya') }}
            </h2>

            @can('create-berkas')
                <a href="{{ route('berkas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <i class="fa-solid fa-plus mr-2"></i>
                    <span>Tambah Berkas Baru</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Bagian Berkas Masuk (Perlu Diterima) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fa-solid fa-inbox text-yellow-500 mr-3"></i> Berkas Masuk (Perlu Diterima)
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="flex items-center space-x-2">
                            <input type="text" name="search_masuk" placeholder="Cari no berkas, pengirim..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ request('search_masuk') }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
                            @if(request('search_masuk'))
                                <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900" title="Reset Pencarian">Reset</a>
                            @endif
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomer Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dikirim Dari</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Permohonan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasMenunggu as $berkas)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-gray-800">{{ $berkas->nomer_berkas }}</p>
                                            <p class="text-xs text-gray-500">Dikirim: {{ $berkas->updated_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-gray-800">{{ $berkas->pengirim->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ optional($berkas->pengirim->jabatan)->nama_jabatan ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-normal"><p class="text-sm font-semibold text-gray-800">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'N/A' }}</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <form action="{{ route('berkas.terima', $berkas) }}" method="POST">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700" title="Terima Berkas"><i class="fa-solid fa-check mr-2"></i> Terima</button></form>
                                                <form action="{{ route('berkas.tolak', $berkas) }}" method="POST">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700" title="Tolak & Kembalikan Berkas"><i class="fa-solid fa-times mr-2"></i> Tolak</button></form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada berkas masuk yang cocok dengan pencarian Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Bagian Berkas di Meja Saya --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                      <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fa-solid fa-file-signature text-blue-500 mr-3"></i> Berkas di Meja Saya
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="flex items-center space-x-2">
                            <input type="text" name="search_di_meja" placeholder="Cari no berkas, pemohon..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ request('search_di_meja') }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
                            @if(request('search_di_meja'))
                                <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900" title="Reset Pencarian">Reset</a>
                            @endif
                        </form>
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end items-center mb-4">
                        <form id="bulk-kirim-form" action="{{ route('berkas.kirim') }}" method="POST" class="flex items-center space-x-2">
                            @csrf
                            <input type="hidden" name="berkas_ids" id="berkas-ids-input">
                            <select name="tujuan_user_id" id="tujuan-user-id-select" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" style="min-width: 180px;"><option value="">-- Kirim Ke --</option>@foreach($daftarUserTujuan as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
                            <input type="text" name="catatan_pengiriman" placeholder="Catatan (opsional)..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700" title="Kirim Berkas yang Dipilih">Kirim Terpilih</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left"><input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"></th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomer Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Permohonan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi Lainnya</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasDiMeja as $berkas)
                                     <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4"><input type="checkbox" name="berkas_id[]" value="{{ $berkas->id }}" class="berkas-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><a href="{{ route('berkas.show', $berkas) }}" class="text-sm font-semibold text-indigo-600 hover:underline">{{ $berkas->nomer_berkas }}</a><p class="text-xs text-gray-500">Diterima: {{ $berkas->updated_at->format('d M Y, H:i') }}</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><p class="text-sm font-semibold text-gray-800">{{ $berkas->nama_pemohon }}</p><p class="text-xs text-gray-500">{{ $berkas->desa }}, {{ $berkas->kecamatan }}</p></td>
                                        <td class="px-6 py-4 whitespace-normal"><p class="text-sm font-semibold text-gray-800">{{ optional($berkas->jenisPermohonan)->nama_permohonan ?? 'N/A' }}</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                
                                                {{-- [UPDATE] TOMBOL EDIT (Hanya Role Tertentu) --}}
                                                @if(in_array(optional(Auth::user()->jabatan)->nama_jabatan, ['Petugas Loket','Petugas Loket Entri', 'Petugas Loket Penyerahan', 'Admin'])) 
                                                    <a href="{{ route('berkas.edit', $berkas->id) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700" title="Edit Berkas">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                @endif

                                                {{-- [UPDATE] TOMBOL KIRIM WA DENGAN MODAL --}}
                                                <button type="button" 
                                                        onclick="openWaModal('{{ $berkas->nomer_wa }}', '{{ $berkas->nama_pemohon }}', '{{ $berkas->nomer_berkas }}', '{{ $berkas->status }}')"
                                                        class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-xs font-semibold rounded-md hover:bg-green-600" 
                                                        title="Kirim WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </button>

                                                {{-- TOMBOL LAMA --}}
                                                <form action="{{ route('berkas.pending', $berkas) }}" method="POST" class="inline" onsubmit="return handleAksiDenganCatatan(this, 'pending');">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600" title="Tunda Berkas"><i class="fa-solid fa-pause"></i></button></form>
                                                
                                                @if(optional(Auth::user()->jabatan)->nama_jabatan === 'Petugas Loket Penyerahan')
                                                    <form action="{{ route('berkas.selesaikan', $berkas) }}" method="POST" class="inline">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700" title="Selesaikan Berkas"><i class="fa-solid fa-check-double"></i></button></form>
                                                @endif
                                                
                                                <form action="{{ route('berkas.tutup', $berkas) }}" method="POST" class="inline" onsubmit="return handleAksiDenganCatatan(this, 'tutup');">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-xs font-semibold rounded-md hover:bg-gray-700" title="Tutup Berkas"><i class="fa-solid fa-archive"></i></button></form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada berkas di meja Anda yang cocok dengan pencarian Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Bagian Berkas yang Ditunda (Pending) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fa-solid fa-clock-rotate-left text-orange-500 mr-3"></i> Berkas yang Ditunda (Pending)
                        </h3>
                        <form action="{{ route('ruang-kerja') }}" method="GET" class="flex items-center space-x-2">
                            <input type="text" name="search_ditunda" placeholder="Cari no berkas, pemohon..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ request('search_ditunda') }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
                            @if(request('search_ditunda'))
                                <a href="{{ route('ruang-kerja') }}" class="text-sm text-gray-600 hover:text-gray-900" title="Reset Pencarian">Reset</a>
                            @endif
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomer Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail Pemohon</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ditunda Sejak</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($berkasDitunda as $berkas)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap"><a href="{{ route('berkas.show', $berkas) }}" class="text-sm font-semibold text-indigo-600 hover:underline">{{ $berkas->nomer_berkas }}</a></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><p class="text-sm font-semibold text-gray-800">{{ $berkas->nama_pemohon }}</p><p class="text-xs text-gray-500">{{ $berkas->desa }}, {{ $berkas->kecamatan }}</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><p class="text-sm text-gray-800">{{ $berkas->updated_at->format('d M Y, H:i') }}</p><p class="text-xs text-gray-500">({{ $berkas->updated_at->diffForHumans() }})</p></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form action="{{ route('berkas.aktifkan', $berkas) }}" method="POST">@csrf<button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700" title="Aktifkan Kembali Berkas"><i class="fa-solid fa-play mr-2"></i> Aktifkan</button></form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada berkas yang ditunda yang cocok dengan pencarian Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL PILIH TEMPLATE WA --}}
    <div id="waModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Background Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeWaModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content --}}
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Kirim WhatsApp</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-4">Pilih template pesan yang akan dikirim:</p>
                        
                        {{-- Select Template --}}
                        <select id="waTemplateSelect" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="updatePreview()">
                            <option value="">-- Pilih Template --</option>
                            {{-- Opsi diisi via JS --}}
                        </select>

                        {{-- Preview Pesan --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Preview Pesan:</label>
                            <textarea id="waMessagePreview" rows="6" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="sendWhatsapp()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fa-brands fa-whatsapp mr-2"></i> Kirim Sekarang
                    </button>
                    <button type="button" onclick="closeWaModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // --- LOGIKA FORM AKSI ---
        function handleAksiDenganCatatan(form, aksi) {
            event.preventDefault(); 
            const pesan = aksi === 'pending' ? 'Masukkan alasan menunda berkas:' : 'Masukkan catatan untuk menutup berkas:';
            const catatan = prompt(pesan);
            if (catatan === null || catatan.trim() === '') {
                if (catatan !== null) { alert('Aksi dibatalkan karena catatan tidak diisi.'); }
                return false; 
            }
            let hiddenInput = form.querySelector('input[name="catatan_aksi"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'catatan_aksi';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = catatan;
            form.submit(); 
        }

        // --- LOGIKA BULK ACTION ---
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const berkasCheckboxes = document.querySelectorAll('.berkas-checkbox');
            const bulkKirimForm = document.getElementById('bulk-kirim-form');
            const berkasIdsInput = document.getElementById('berkas-ids-input');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('click', function () {
                    berkasCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                });
            }
            if (bulkKirimForm) {
                bulkKirimForm.addEventListener('submit', function (e) {
                    const selectedIds = [];
                    berkasCheckboxes.forEach(checkbox => { if (checkbox.checked) { selectedIds.push(checkbox.value); } });
                    if (selectedIds.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih setidaknya satu berkas untuk dikirim.');
                        return;
                    }
                    if (document.getElementById('tujuan-user-id-select').value === '') {
                        e.preventDefault();
                        alert('Silakan pilih tujuan pengiriman.');
                        return;
                    }
                    berkasIdsInput.value = selectedIds.join(',');
                });
            }
        });

        // --- LOGIKA WA TEMPLATE (MODAL) ---
        let currentWaData = { phone: '', nama: '', berkas: '', status: '' };
        let templatesData = [];

        function openWaModal(phone, nama, berkas, status) {
            // Bersihkan nomor HP
            phone = phone.replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);

            currentWaData = { phone, nama, berkas, status };
            
            // Tampilkan Modal
            document.getElementById('waModal').classList.remove('hidden');

            // Ambil Template via AJAX
            fetch("{{ route('api.wa-templates') }}")
                .then(response => response.json())
                .then(data => {
                    templatesData = data;
                    const select = document.getElementById('waTemplateSelect');
                    select.innerHTML = '<option value="">-- Pilih Template --</option>';
                    data.forEach((tpl, index) => {
                        select.innerHTML += `<option value="${index}">${tpl.judul}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching templates:', error);
                    alert('Gagal mengambil data template.');
                });
        }

        function closeWaModal() {
            document.getElementById('waModal').classList.add('hidden');
            document.getElementById('waTemplateSelect').value = "";
            document.getElementById('waMessagePreview').value = "";
        }

        function updatePreview() {
            const index = document.getElementById('waTemplateSelect').value;
            if (index === "") {
                document.getElementById('waMessagePreview').value = "";
                return;
            }

            let pesan = templatesData[index].pesan;

            // Ganti Placeholder dengan Data Asli
            pesan = pesan.replace(/{nama_pemohon}/g, currentWaData.nama)
                         .replace(/{nomer_berkas}/g, currentWaData.berkas)
                         .replace(/{status}/g, currentWaData.status);

            document.getElementById('waMessagePreview').value = pesan;
        }

        function sendWhatsapp() {
            const pesan = document.getElementById('waMessagePreview').value;
            if (!pesan) {
                alert('Silakan pilih template terlebih dahulu.');
                return;
            }
            
            if (!currentWaData.phone) {
                alert('Nomer WhatsApp tidak tersedia untuk pemohon ini.');
                return;
            }

            const url = `https://wa.me/${currentWaData.phone}?text=${encodeURIComponent(pesan)}`;
            window.open(url, '_blank');
            closeWaModal();
        }
    </script>
    @endpush
</x-app-layout>