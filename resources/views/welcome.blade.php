<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Tracking Berkas</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 text-gray-800">
    <div class="relative min-h-screen flex flex-col items-center justify-center">
        
        <div class="absolute top-0 right-0 p-6 text-right">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                    @endif
                @endauth
            @endif
        </div>

        <main class="flex-grow flex flex-col items-center justify-center text-center px-6">
            
            <div class="max-w-3xl">
                <i class="fa-solid fa-folder-tree text-5xl text-indigo-500 mb-4"></i>
                <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 mb-4">
                    Sistem Pelacakan Berkas Digital
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8">
                    Monitor pergerakan dokumen penting Anda secara real-time, dari meja ke meja, dengan mudah dan transparan.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-block bg-indigo-600 text-white font-bold text-lg px-8 py-4 rounded-lg shadow-lg hover:bg-indigo-700 transition-transform transform hover:scale-105">
                        Masuk ke Aplikasi
                    </a>
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-block bg-white text-indigo-600 font-bold text-lg px-8 py-4 rounded-lg shadow-lg hover:bg-gray-100 transition-transform transform hover:scale-105 border border-gray-200">
                        Daftar Akun Baru
                    </a>
                </div>
            </div>

            <div class="max-w-7xl mx-auto mt-24 px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 bg-indigo-100 text-indigo-600 rounded-full mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Lacak Real-Time</h3>
                        <p class="text-gray-600">
                            Ketahui posisi pasti berkas Anda setiap saat, lengkap dengan riwayat perjalanan yang detail.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 bg-indigo-100 text-indigo-600 rounded-full mx-auto mb-4">
                             <i class="fa-solid fa-chart-simple text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Monitoring Mudah</h3>
                        <p class="text-gray-600">
                            Dashboard interaktif memberikan gambaran umum status berkas dan mengidentifikasi potensi hambatan.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 bg-indigo-100 text-indigo-600 rounded-full mx-auto mb-4">
                            <i class="fa-solid fa-shield-halved text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Aman & Terkendali</h3>
                        <p class="text-gray-600">
                            Dengan sistem approval, hanya pengguna terverifikasi yang dapat mengakses dan memproses berkas.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="w-full text-center p-6 text-sm text-gray-500">
            © {{ date('Y') }} Sistem Tracking Berkas. All Rights Reserved.
        </footer>
    </div>
</body>
</html>