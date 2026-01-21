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
                                Memeriksa...
                            </span>
                        </div>

                        <div class="flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 relative p-4">
                            
                            {{-- Area Image QR --}}
                            <img id="qr-code" src="" alt="QR Code" class="w-64 h-64 object-contain hidden shadow-lg rounded-lg">
                            
                            {{-- Loading State --}}
                            <div id="loader" class="flex flex-col items-center">
                                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-indigo-500 mb-3"></i>
                                <p class="text-sm text-gray-500">Menghubungkan ke Server WA...</p>
                            </div>

                            {{-- Connected State --}}
                            <div id="connected-state" class="hidden flex-col items-center text-center">
                                <div class="h-16 w-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-brands fa-whatsapp text-3xl"></i>
                                </div>
                                <h4 class="font-bold text-green-700 text-lg">Terhubung!</h4>
                                <p class="text-sm text-gray-500 mt-1">WhatsApp siap digunakan mengirim notifikasi.</p>
                                
                                <form action="{{ route('whatsapp.logout') }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition">
                                        <i class="fa-solid fa-power-off mr-2"></i> Logout Device
                                    </button>
                                </form>
                            </div>

                        </div>
                        
                        <div class="mt-4 text-xs text-gray-400 text-center">
                            Scan QR Code menggunakan aplikasi WhatsApp di HP Anda (Menu Linked Devices).
                        </div>
                    </div>
                </div>

                {{-- Kanan: Test Kirim Pesan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-700 mb-4">Tes Pengiriman Pesan</h3>
                        
                        @if(session('success'))
                            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('whatsapp.send-test') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <x-input-label for="number" :value="__('Nomor Tujuan (Ex: 08123...)')" />
                                <x-text-input id="number" class="block mt-1 w-full" type="text" name="number" required placeholder="08xxxxxxxx" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="message" :value="__('Pesan')" />
                                <textarea name="message" id="message" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Halo, ini pesan tes..."></textarea>
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>
                                    <i class="fa-regular fa-paper-plane mr-2"></i> {{ __('Kirim Pesan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Script Socket.io / Polling untuk QR --}}
    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const socket = io("{{ $waUrl }}"); // URL Server Node.js WA
            const qrImage = document.getElementById('qr-code');
            const loader = document.getElementById('loader');
            const connectedState = document.getElementById('connected-state');
            const statusLabel = document.getElementById('connection-status');

            // Listen QR
            socket.on('qr', (src) => {
                qrImage.src = src;
                qrImage.classList.remove('hidden');
                loader.classList.add('hidden');
                connectedState.classList.add('hidden');
                
                statusLabel.innerText = "Scan QR Sekarang";
                statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-yellow-200 text-yellow-700";
            });

            // Listen Ready
            socket.on('ready', () => {
                qrImage.classList.add('hidden');
                loader.classList.add('hidden');
                connectedState.classList.remove('hidden');
                connectedState.classList.add('flex');

                statusLabel.innerText = "Terhubung";
                statusLabel.className = "px-3 py-1 rounded-full text-xs font-bold bg-green-200 text-green-700";
            });

            // Listen Authenticated
            socket.on('authenticated', () => {
                statusLabel.innerText = "Authenticating...";
            });
            
            // Initial Check (Optional, tergantung implementasi API WA Anda)
            // Bisa fetch ke endpoint /status di WA Gateway
        });
    </script>
</x-app-layout>