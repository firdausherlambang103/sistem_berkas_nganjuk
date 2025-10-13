<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Rinci Posisi Berkas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-8">
                    
                    {{-- Loop melalui setiap jabatan --}}
                    @foreach ($jabatans as $jabatan)
                        {{-- Tampilkan grup jabatan hanya jika ada user di dalamnya yang memegang berkas --}}
                        @if($jabatan->users->isNotEmpty())
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">
                                    {{ $jabatan->nama_jabatan }}
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    {{-- Loop melalui setiap user dalam jabatan tersebut --}}
                                    @foreach ($jabatan->users->sortBy('name') as $user)
                                        <a href="{{ route('laporan.berkas_by_user', $user) }}" class="block bg-gray-50 border border-gray-200 rounded-lg p-4 text-center hover:bg-indigo-50 hover:shadow-md transition-all duration-200">
                                            <p class="text-base font-semibold text-gray-800 truncate" title="{{ $user->name }}">{{ $user->name }}</p>
                                            <p class="text-3xl font-bold mt-2">{{ $user->berkas_di_tangan_count }}</p>
                                            <p class="text-xs text-gray-500">Berkas Aktif</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

