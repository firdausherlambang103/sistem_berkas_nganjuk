<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp Gateway Scan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kiri: Area Scan QR --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-700">Status Koneksi</h3>
                            <span id="connection-status" class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600 animate-pulse">
                                Menghubungkan...
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 relative p-4">
                            <img id="qr-code" src="" alt="QR Code" class="w-64 h-64 object-contain hidden shadow-lg rounded-lg">
                            
                            <div id="loader" class="flex flex-col items-center">
                                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-indigo-500 mb-3"></i>
                                <p class="text-sm text-gray-500">Mencari Server WA di <br><strong>{{ $waUrl ?? '192.168.100.15:3000' }}</strong>...</p>
                            </div>

                            <div id="connected-state" class="hidden flex-col items-center text-center">
                                <div class="h-16 w-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-brands fa-whatsapp text-3xl"></i>
                                </div>
                                <h4 class="font-bold text-green-700 text-lg">Terhubung!</h4>
                                <form action="{{ route('admin.whatsapp.logout') }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Test Kirim Pesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Tes Pesan</h3>
                        {{-- Notifikasi --}}
                        @if(session('success'))
                            <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('admin.whatsapp.send-test') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block font-medium text-sm text-gray-700">Nomor Tujuan</label>
                                <input type="text" name="number" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required placeholder="08xxxxxxxx" />
                            </div>
                            <div class="mb-4">
                                <label class="block font-medium text-sm text-gray-700">Pesan</label>
                                <textarea name="message" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>Tes koneksi WA Gateway</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Kirim</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // PENTING: Gunakan IP Server, bukan localhost
            // $waUrl dikirim dari Controller (dari .env), atau fallback ke IP keras
            const socketUrl = "{{ $waUrl ?? 'http://192.168.100.15:3000' }}";
            
            console.log("Connecting to Socket:", socketUrl);

            try {
                const socket = io(socketUrl, {
                    transports: ['websocket', 'polling']
                });
                
                const qrImage = document.getElementById('qr-code');
                const loader = document.getElementById('loader');
                const connectedState = document.getElementById('connected-state');
                const statusLabel = document.getElementById('connection-status');

                socket.on('connect', () => {
                    console.log('Socket Connected!');
                    statusLabel.innerText = "Terhubung ke Server";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-blue-200 text-blue-700";
                });

                socket.on('qr', (src) => {
                    qrImage.src = src;
                    qrImage.classList.remove('hidden');
                    loader.classList.add('hidden');
                    connectedState.classList.add('hidden');
                    statusLabel.innerText = "Scan QR Sekarang";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-yellow-200 text-yellow-700";
                });

                socket.on('ready', () => {
                    qrImage.classList.add('hidden');
                    loader.classList.add('hidden');
                    connectedState.classList.remove('hidden');
                    connectedState.classList.add('flex');
                    statusLabel.innerText = "WA Siap";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-green-200 text-green-700";
                });

                socket.on('connect_error', (err) => {
                    console.error('Socket Error:', err);
                    statusLabel.innerText = "Gagal Koneksi";
                    statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-red-200 text-red-700";
                });

            } catch (error) {
                console.error("Critical Socket Error:", error);
            }
        });
    </script>
</x-app-layout>