<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    
                    <h3 class="text-lg font-bold mb-4">Koneksi WhatsApp Web</h3>

                    <div id="wa-container" class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 min-h-[300px]">
                        
                        <div id="loading-area" class="hidden">
                            <svg class="animate-spin h-10 w-10 text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-600 font-semibold" id="loading-text">Menghubungkan ke Server...</p>
                        </div>

                        <div id="qr-area" class="hidden flex-col items-center">
                            <div id="qrcode" class="bg-white p-2 rounded shadow-md mb-4"></div>
                            <p class="text-gray-700">Silakan scan QR Code ini dengan WhatsApp di HP Anda.</p>
                            <p class="text-xs text-gray-500 mt-2">Buka WA > Titik Tiga > Perangkat Tertaut > Tautkan Perangkat</p>
                        </div>

                        <div id="connected-area" class="hidden flex-col items-center">
                            <div class="bg-green-100 p-4 rounded-full mb-4">
                                <i class="fa-brands fa-whatsapp text-6xl text-green-600"></i>
                            </div>
                            <h4 class="text-2xl font-bold text-green-700 mb-2">Terhubung!</h4>
                            <p class="text-gray-600 mb-6">WhatsApp Server siap digunakan untuk mengirim pesan.</p>
                            
                            <button onclick="logoutWA()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout Perangkat
                            </button>
                        </div>

                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500">
                        Status Server: <span id="server-status" class="font-mono bg-gray-200 px-2 py-1 rounded">Connecting...</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Socket.IO Client --}}
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    {{-- QRCode.js untuk render QR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        // Koneksi ke Node.js Server (Port 3000)
        const socket = io("http://192.168.100.15:3000");
        
        const loadingArea = document.getElementById('loading-area');
        const qrArea = document.getElementById('qr-area');
        const connectedArea = document.getElementById('connected-area');
        const statusLabel = document.getElementById('server-status');
        const loadingText = document.getElementById('loading-text');
        let qrCodeObj = null;

        // --- Helper: Tampilkan Area Tertentu ---
        function showArea(areaName) {
            loadingArea.classList.add('hidden');
            qrArea.classList.add('hidden');
            connectedArea.classList.add('hidden');

            if (areaName === 'loading') loadingArea.classList.remove('hidden');
            if (areaName === 'qr') qrArea.classList.remove('hidden');
            if (areaName === 'connected') connectedArea.classList.remove('hidden');
        }

        // --- SOCKET EVENTS ---

        socket.on('connect', () => {
            console.log("Terhubung ke Socket Server");
            statusLabel.innerText = "Connected to Server";
            statusLabel.classList.replace('bg-gray-200', 'bg-blue-200');
        });

        socket.on('disconnect', () => {
            console.log("Terputus dari Socket Server");
            statusLabel.innerText = "Disconnected";
            statusLabel.classList.replace('bg-blue-200', 'bg-red-200');
            showArea('loading');
            loadingText.innerText = "Koneksi ke Server WA terputus...";
        });

        // Menerima Pesan Status
        socket.on('message', (msg) => {
            console.log("Message:", msg);
            loadingText.innerText = msg;
        });

        // Menerima QR Code String
        socket.on('qr_code', (qrStr) => {
            console.log("QR Diterima");
            showArea('qr');
            statusLabel.innerText = "Menunggu Scan";

            // Render QR Code
            const qrContainer = document.getElementById("qrcode");
            qrContainer.innerHTML = ""; // Bersihkan QR lama
            
            // Generate baru
            new QRCode(qrContainer, {
                text: qrStr,
                width: 256,
                height: 256,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });

        // Menerima Perubahan Status WA
        socket.on('status', (status) => {
            console.log("Status WA:", status);
            
            if (status === 'ready' || status === 'authenticated') {
                showArea('connected');
                statusLabel.innerText = "WhatsApp Ready";
                statusLabel.classList.replace('bg-blue-200', 'bg-green-200');
            } 
            else if (status === 'scan_qr') {
                showArea('qr');
            } 
            else {
                showArea('loading');
            }
        });

        // --- FUNGSI LOGOUT ---
        function logoutWA() {
            if(!confirm('Apakah Anda yakin ingin logout dari WhatsApp Web?')) return;

            // Kirim request logout ke Node.js via API (karena PHP/Laravel ada di port 8000, Node di 3000)
            // Kita bisa pakai fetch langsung ke nodejs endpoint
            fetch('http://192.168.100.15:3000/logout', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                showArea('loading');
                loadingText.innerText = "Menunggu QR Code baru...";
            })
            .catch(err => {
                console.error(err);
                alert("Gagal logout server.");
            });
        }
    </script>
</x-app-layout>