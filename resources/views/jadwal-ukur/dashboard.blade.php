<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-calendar-alt mr-2"></i>
            Dashboard Penjadwalan Ukur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>Selamat datang di modul Penjadwalan Ukur.</p>
                    <p class="mt-4">
                        <a href="{{ route('jadwal-ukur.pilih-petugas') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Mulai Buat Jadwal Baru
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
