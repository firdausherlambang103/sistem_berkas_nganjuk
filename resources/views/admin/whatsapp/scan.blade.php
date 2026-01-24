<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-brands fa-whatsapp text-green-500 mr-2"></i> {{ __('WhatsApp Gateway Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notifikasi --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 text-green-700 shadow-sm rounded-r-lg">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 shadow-sm rounded-r-lg">
                    <p class="font-bold">Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- KIRI: Status & Kontrol --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status Koneksi</h3>
                    
                    <div class="flex items-center mb-6">
                        <div id="status-indicator" class="w-4 h-4 rounded-full bg-gray-400 mr-3"></div>
                        <span id="status-text" class="text-lg font-semibold text-gray-600">Memeriksa status...</span>
                    </div>

                    <div class="bg-gray-50 p-4 rounded border border-gray-200 text-sm text-gray-600 mb-6">
                        <p><strong>Info:</strong> Halaman ini menghubungkan sistem dengan WhatsApp Gateway.</p>
                        <ul class="list-disc ml-5 mt-2">
                            <li>Jika status <strong>Terhubung</strong>, sistem siap mengirim pesan.</li>
                            <li>Jika status <strong>Terputus</strong>, silakan scan QR Code di sebelah kanan.</li>
                        </ul>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="checkStatus()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                            <i class="fa-solid fa-sync mr-2"></i> Cek Status
                        </button>

                        <form action="{{ route('whatsapp.logout') }}" method="POST" onsubmit="return confirm('Yakin ingin memutus koneksi WhatsApp?');">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                                <i class="fa-solid fa-power-off mr-2"></i> Logout / Disconnect
                            </button>
                        </form>
                    </div>
                    
                    <hr class="my-6">
                    
                    <h3 class="text-md font-bold mb-2">Test Kirim Pesan</h3>
                    <form action="{{ route('whatsapp.send-test') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="number" placeholder="08xxxxx (Nomor HP)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm flex-1 text-sm">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">Kirim</button>
                    </form>
                </div>

                {{-- KANAN: Area QR Code --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col items-center justify-center text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scan QR Code</h3>
                    
                    <div id="qr-container" class="border-2 border-dashed border-gray-300 rounded-lg p-4 w-64 h-64 flex items-center justify-center bg-gray-50">
                        <span class="text-gray-400 text-sm" id="qr-loading">Menunggu data QR...</span>
                        <img id="qr-image" src="" alt="QR Code" class="hidden max-w-full h-auto rounded">
                    </div>

                    <p class="mt-4 text-sm text-gray-500">
                        Buka WhatsApp di HP Anda -> Menu -> Perangkat Tertaut -> Tautkan Perangkat.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // URL Endpoint dari Controller (Kita akan buat route ini di langkah 2)
        const statusUrl = "{{ route('whatsapp.check-status') }}"; 
        const qrUrl = "{{ route('whatsapp.get-qr') }}";

        function checkStatus() {
            const indicator = document.getElementById('status-indicator');
            const text = document.getElementById('status-text');
            const qrContainer = document.getElementById('qr-container');
            const qrLoading = document.getElementById('qr-loading');
            const qrImage = document.getElementById('qr-image');

            // Set loading state
            text.innerText = "Memuat...";
            
            fetch(statusUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.connected) {
                        // KONEKSI AKTIF
                        indicator.classList.remove('bg-gray-400', 'bg-red-500');
                        indicator.classList.add('bg-green-500');
                        text.innerText = "Terhubung / Connected";
                        text.classList.add('text-green-600');
                        
                        // Sembunyikan QR jika sudah connect
                        qrImage.classList.add('hidden');
                        qrLoading.innerText = "Perangkat sudah terhubung.";
                        qrContainer.classList.add('bg-green-50');
                    } else {
                        // KONEKSI PUTUS -> AMBIL QR
                        indicator.classList.remove('bg-gray-400', 'bg-green-500');
                        indicator.classList.add('bg-red-500');
                        text.innerText = "Terputus / Disconnected";
                        text.classList.remove('text-green-600');
                        
                        // Fetch QR Code
                        loadQrCode();
                    }
                })
                .catch(err => {
                    console.error(err);
                    text.innerText = "Gagal menghubungi Gateway.";
                    indicator.classList.add('bg-gray-400');
                });
        }

        function loadQrCode() {
            const qrImage = document.getElementById('qr-image');
            const qrLoading = document.getElementById('qr-loading');

            fetch(qrUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.qr_code) {
                        // Jika data QR berupa base64 image string
                        qrImage.src = data.qr_code;
                        qrImage.classList.remove('hidden');
                        qrLoading.classList.add('hidden');
                    } else if (data.message) {
                        qrLoading.innerText = data.message;
                        qrImage.classList.add('hidden');
                    }
                })
                .catch(err => {
                    qrLoading.innerText = "Gagal memuat QR.";
                });
        }

        // Jalankan saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            checkStatus();
            // Refresh status setiap 10 detik
            setInterval(checkStatus, 10000); 
        });
    </script>
    @endpush
</x-app-layout>