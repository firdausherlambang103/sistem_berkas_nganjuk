<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp Gateway Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notifikasi --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 text-green-700 shadow-sm rounded-r-lg">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 shadow-sm rounded-r-lg">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kiri: Area Scan QR --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                    <div class="p-6 text-gray-900 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                                <i class="fa-solid fa-qrcode text-indigo-500"></i> Status Koneksi
                            </h3>
                            <span id="connection-status" class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600 animate-pulse border border-gray-300">
                                Menghubungkan...
                            </span>
                        </div>

                        <div class="flex-1 flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 relative p-6 transition-all" id="qr-container">
                            
                            {{-- Area Image QR --}}
                            <img id="qr-code" src="" alt="QR Code" class="w-64 h-64 object-contain hidden shadow-xl rounded-lg border-4 border-white">
                            
                            {{-- Loading State --}}
                            <div id="loader" class="flex flex-col items-center text-center">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin"></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-4 font-medium">Mencari Server WA di:<br><code class="bg-gray-200 px-2 py-1 rounded text-xs mt-1 inline-block">{{ $waUrl }}</code></p>
                            </div>

                            {{-- Connected State --}}
                            <div id="connected-state" class="hidden flex-col items-center text-center">
                                <div class="h-20 w-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4 shadow-sm animate-bounce-slow">
                                    <i class="fa-brands fa-whatsapp text-4xl"></i>
                                </div>
                                <h4 class="font-bold text-green-700 text-xl">WhatsApp Terhubung!</h4>
                                <p class="text-sm text-gray-500 mt-2 max-w-xs">Gateway siap digunakan. Anda dapat mengirim pesan notifikasi sekarang.</p>
                                
                                <form action="{{ route('admin.whatsapp.logout') }}" method="POST" class="mt-6">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        <i class="fa-solid fa-power-off mr-2"></i> Putuskan Koneksi
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="mt-6 text-xs text-gray-400 text-center bg-blue-50 p-3 rounded-lg border border-blue-100 text-blue-600">
                            <i class="fa-solid fa-circle-info mr-1"></i> Buka WhatsApp di HP Anda > Menu > Perangkat Tertaut > Tautkan Perangkat.
                        </div>
                    </div>
                </div>

                {{-- Kanan: Test Kirim Pesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-6 flex items-center gap-2">
                            <i class="fa-regular fa-paper-plane text-indigo-500"></i> Tes Pengiriman
                        </h3>

                        <form action="{{ route('admin.whatsapp.send-test') }}" method="POST">
                            @csrf
                            <div class="mb-5">
                                <x-input-label for="number" :value="__('Nomor Tujuan')" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-phone text-gray-400 text-sm"></i>
                                    </div>
                                    <x-text-input id="number" class="block w-full pl-10" type="text" name="number" required placeholder="Contoh: 081234567890" />
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Gunakan awalan 08 atau 62.</p>
                            </div>

                            <div class="mb-5">
                                <x-input-label for="message" :value="__('Pesan Tes')" />
                                <textarea name="message" id="message" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Halo, ini adalah pesan tes dari sistem berkas..."></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <x-primary-button class="gap-2">
                                    <span>Kirim Pesan</span> <i class="fa-solid fa-arrow-right"></i>
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Script Socket.io --}}
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mengambil URL dari Controller (yang sudah diset ke 192.168.100.15)
            const socketUrl = "{{ $waUrl }}"; 
            
            console.log("Mencoba terhubung ke Socket IO di:", socketUrl);

            const qrImage = document.getElementById('qr-code');
            const loader = document.getElementById('loader');
            const connectedState = document.getElementById('connected-state');
            const statusLabel = document.getElementById('connection-status');
            const qrContainer = document.getElementById('qr-container');

            try {
                // Inisialisasi Socket
                const socket = io(socketUrl, {
                    transports: ['websocket', 'polling'], // Paksa transport yang stabil
                    reconnection: true,
                    reconnectionAttempts: 5
                }); 

                // Event: Connect
                socket.on('connect', () => {
                    console.log('Socket Connected ID:', socket.id);
                    statusLabel.innerText = "Menunggu QR...";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200";
                });

                // Event: Terima QR Code
                socket.on('qr', (src) => {
                    console.log("QR Code diterima");
                    qrImage.src = src;
                    qrImage.classList.remove('hidden');
                    loader.classList.add('hidden');
                    connectedState.classList.add('hidden');
                    
                    statusLabel.innerText = "Silakan Scan";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200 animate-pulse";
                    qrContainer.classList.remove('bg-gray-50');
                    qrContainer.classList.add('bg-white');
                });

                // Event: WA Sudah Ready (Sudah Login)
                socket.on('ready', (msg) => {
                    console.log("WA Ready:", msg);
                    qrImage.classList.add('hidden');
                    loader.classList.add('hidden');
                    connectedState.classList.remove('hidden');
                    connectedState.classList.add('flex');

                    statusLabel.innerText = "Terhubung";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200";
                });

                // Event: Authenticated
                socket.on('authenticated', () => {
                    console.log("WA Authenticated");
                    statusLabel.innerText = "Otentikasi...";
                });

                // Event: Error Koneksi
                socket.on('connect_error', (err) => {
                    console.error("Gagal konek ke Socket IO:", err);
                    statusLabel.innerText = "Server Offline";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200";
                });

            } catch (error) {
                console.error("Critical Error:", error);
            }
        });
    </script>
</x-app-layout>