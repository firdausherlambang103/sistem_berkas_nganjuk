<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-brands fa-whatsapp text-green-500 mr-2"></i> {{ __('WhatsApp Gateway Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notifikasi Flash Message --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 text-green-700 shadow-sm rounded-r-lg flex justify-between items-center">
                    <div>
                        <p class="font-bold">Berhasil</p>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-times"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 shadow-sm rounded-r-lg flex justify-between items-center">
                    <div>
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-times"></i></button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- KOLOM KIRI: Status & Kontrol --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Status Koneksi</h3>
                    
                    {{-- Indikator Status --}}
                    <div class="flex items-center mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div id="status-indicator" class="w-5 h-5 rounded-full bg-gray-400 mr-3 transition-colors duration-500"></div>
                        <span id="status-text" class="text-lg font-bold text-gray-600">Memeriksa status...</span>
                    </div>

                    {{-- Informasi --}}
                    <div class="bg-blue-50 border border-blue-200 text-sm text-blue-800 p-4 rounded-lg mb-6">
                        <p class="font-bold mb-1"><i class="fa-solid fa-circle-info mr-1"></i> Petunjuk:</p>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>Pastikan aplikasi <strong>WA Gateway</strong> (Node.js) sudah berjalan di server.</li>
                            <li>Jika status <strong>Terputus</strong>, tunggu QR Code muncul di sebelah kanan lalu scan menggunakan HP.</li>
                            <li>Jika status <strong>Terhubung</strong>, sistem siap mengirim notifikasi.</li>
                        </ul>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex gap-3 mb-8">
                        <button onclick="checkStatus()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                            <i class="fa-solid fa-sync mr-2" id="btn-refresh-icon"></i> Refresh Status
                        </button>

                        <form action="{{ route('admin.whatsapp.logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutus koneksi WhatsApp?');">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                                <i class="fa-solid fa-power-off mr-2"></i> Logout / Disconnect
                            </button>
                        </form>
                    </div>
                    
                    <hr class="border-gray-200 mb-6">
                    
                    {{-- Form Test Kirim --}}
                    <h3 class="text-md font-bold text-gray-800 mb-3"><i class="fa-regular fa-paper-plane mr-1"></i> Test Kirim Pesan</h3>
                    <form action="{{ route('admin.whatsapp.send-test') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="number" placeholder="Contoh: 08123456789" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm flex-1 text-sm" required>
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition font-semibold">Kirim</button>
                    </form>
                </div>

                {{-- KOLOM KANAN: Area QR Code --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col items-center text-center">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2 w-full text-left">Scan QR Code</h3>
                    
                    <div id="qr-container" class="border-2 border-dashed border-gray-300 rounded-xl p-4 w-72 h-72 flex items-center justify-center bg-gray-50 relative mb-4 transition-all duration-300">
                        
                        {{-- Loading State --}}
                        <div id="qr-loading" class="absolute inset-0 flex flex-col items-center justify-center">
                            <i class="fa-solid fa-circle-notch fa-spin text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">Menunggu data server...</p>
                        </div>

                        {{-- QR Image --}}
                        <img id="qr-image" src="" class="hidden w-full h-full object-contain rounded-lg z-10">
                        
                        {{-- Connected State (Hidden by default) --}}
                        <div id="connected-state" class="hidden flex-col items-center justify-center z-20">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-brands fa-whatsapp text-5xl text-green-600"></i>
                            </div>
                            <span class="font-bold text-green-700 text-lg">WhatsApp Terhubung!</span>
                            <p class="text-gray-500 text-xs mt-1">Sesi aktif dan siap digunakan.</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-xs text-left w-full max-w-sm">
                        <strong>Cara Scan:</strong>
                        <ol class="list-decimal ml-4 mt-1">
                            <li>Buka WhatsApp di HP Anda.</li>
                            <li>Ketuk menu <strong>Titik Tiga</strong> (Android) atau <strong>Pengaturan</strong> (iOS).</li>
                            <li>Pilih <strong>Perangkat Tertaut</strong> > <strong>Tautkan Perangkat</strong>.</li>
                            <li>Arahkan kamera ke QR Code di atas.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // === KONFIGURASI URL ===
        // Pastikan nama route sesuai dengan routes/web.php (prefix admin.)
        const statusUrl = "{{ route('admin.whatsapp.check-status') }}"; 
        const qrUrl = "{{ route('admin.whatsapp.get-qr') }}";

        // === FUNGSI UPDATE TAMPILAN ===
        function updateUI(connected, debugStatus = '') {
            const indicator = document.getElementById('status-indicator');
            const text = document.getElementById('status-text');
            
            const qrLoading = document.getElementById('qr-loading');
            const qrImage = document.getElementById('qr-image');
            const connectedState = document.getElementById('connected-state');
            const qrContainer = document.getElementById('qr-container');

            if (connected) {
                // --- STATE: TERHUBUNG ---
                indicator.className = "w-5 h-5 rounded-full mr-3 bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.6)]";
                text.innerHTML = `Terhubung <span class='text-xs font-normal text-gray-500 ml-2'>(${debugStatus})</span>`;
                text.className = "text-lg font-bold text-green-700";
                
                // Ubah kotak QR jadi indikator sukses
                qrLoading.classList.add('hidden');
                qrImage.classList.add('hidden');
                connectedState.classList.remove('hidden');
                connectedState.classList.add('flex');
                
                qrContainer.classList.remove('border-dashed', 'border-gray-300');
                qrContainer.classList.add('border-solid', 'border-green-200', 'bg-green-50');

            } else {
                // --- STATE: TERPUTUS ---
                indicator.className = "w-5 h-5 rounded-full mr-3 bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.6)]";
                text.innerText = "Terputus / Belum Scan";
                text.className = "text-lg font-bold text-red-600";
                
                // Reset kotak QR dan muat QR baru
                connectedState.classList.add('hidden');
                connectedState.classList.remove('flex');
                
                qrContainer.classList.add('border-dashed', 'border-gray-300');
                qrContainer.classList.remove('border-solid', 'border-green-200', 'bg-green-50');

                // Panggil fungsi load QR
                loadQr(); 
            }
        }

        // === FUNGSI FETCH STATUS ===
        function checkStatus() {
            const icon = document.getElementById('btn-refresh-icon');
            if(icon) icon.classList.add('fa-spin'); // Efek loading pada tombol

            fetch(statusUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    // data.connected dari WaService
                    updateUI(data.connected, data.status_text);
                })
                .catch(err => {
                    console.error("Error checking status:", err);
                    document.getElementById('status-text').innerText = "Gateway Tidak Merespon";
                    document.getElementById('status-indicator').className = "w-5 h-5 rounded-full mr-3 bg-gray-400";
                })
                .finally(() => {
                    if(icon) icon.classList.remove('fa-spin');
                });
        }

        // === FUNGSI FETCH QR CODE ===
        function loadQr() {
            const qrImage = document.getElementById('qr-image');
            const qrLoading = document.getElementById('qr-loading');
            
            // Tampilkan loading sebelum fetch
            qrLoading.classList.remove('hidden');
            qrImage.classList.add('hidden');

            fetch(qrUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.qr_code) {
                        // Jika ada data QR (Base64)
                        qrImage.src = data.qr_code;
                        qrImage.classList.remove('hidden');
                        qrLoading.classList.add('hidden');
                    } else {
                        // Jika server merespon tapi belum ada QR (misal lagi inisialisasi)
                        qrLoading.innerHTML = `<i class="fa-solid fa-hourglass-half text-orange-400 mb-2"></i><p>${data.message || 'Menyiapkan QR...'}</p>`;
                    }
                })
                .catch(err => {
                    console.error("Error loading QR:", err);
                    qrLoading.innerHTML = `<i class="fa-solid fa-exclamation-triangle text-red-500 mb-2"></i><p>Gagal memuat QR</p>`;
                });
        }

        // === JALANKAN SAAT HALAMAN SIAP ===
        document.addEventListener('DOMContentLoaded', function() {
            // Cek status pertama kali
            checkStatus();

            // Polling otomatis setiap 5 detik untuk update real-time
            setInterval(() => {
                // Hanya cek status, logika QR ada di dalam checkStatus() jika terputus
                // Kita panggil fetch manual agar tidak mengganggu UI loading button
                fetch(statusUrl)
                    .then(res => res.json())
                    .then(data => updateUI(data.connected, data.status_text))
                    .catch(e => console.log('Background sync failed'));
            }, 5000);
        });
    </script>
    @endpush
</x-app-layout>