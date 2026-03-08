<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                <i class="fa-solid fa-users-cog mr-2 text-indigo-600"></i>
                Manajemen Pengguna
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex items-start sm:items-center animate-fade-in-down" role="alert">
                    <i class="fa-solid fa-check-circle mr-2 mt-1 sm:mt-0"></i>
                    <p class="text-sm sm:text-base">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex items-start sm:items-center animate-fade-in-down" role="alert">
                    <i class="fa-solid fa-exclamation-circle mr-2 mt-1 sm:mt-0"></i>
                    <p class="text-sm sm:text-base">{{ session('error') }}</p>
                </div>
            @endif

            {{-- 1. MENUNGGU PERSETUJUAN --}}
            <div>
                <div class="flex items-center mb-4 space-x-2">
                    <div class="w-1 h-6 bg-indigo-600 rounded"></div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Menunggu Persetujuan</h3>
                    @if($pendingUsers->count() > 0)
                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-xs font-bold">{{ $pendingUsers->count() }}</span>
                    @endif
                </div>

                @if($pendingUsers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($pendingUsers as $user)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition duration-300">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-gray-800">{{ $user->name }}</h4>
                                        <p class="text-sm text-gray-500 break-all">{{ $user->email }}</p>
                                        <div class="text-xs text-gray-400 mt-2 flex items-center">
                                            <i class="flex-shrink-0 fa-regular fa-clock mr-1"></i>
                                            {{ $user->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-full">
                                        <i class="fa-solid fa-user-clock"></i>
                                    </div>
                                </div>
                                <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="mt-4">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                        <i class="fa-solid fa-check mr-2"></i> Setujui
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                        <p class="text-gray-500 text-sm">Tidak ada permohonan baru.</p>
                    </div>
                @endif
            </div>

            {{-- 2. PEGAWAI INTERNAL --}}
            <div>
                <div class="flex items-center mb-4 space-x-2">
                    <div class="w-1 h-6 bg-blue-600 rounded"></div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Daftar Pegawai Internal</h3>
                </div>

                @forelse ($internalUsers as $jabatan => $users)
                    <div class="mb-6">
                        <div class="bg-gray-100 px-4 py-3 rounded-t-xl border border-gray-200 flex justify-between items-center">
                            <h4 class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                                <i class="fa-solid fa-id-badge mr-2 text-blue-500"></i> {{ $jabatan }}
                            </h4>
                            <span class="bg-blue-200 text-blue-800 text-xs px-2.5 py-1 rounded-full font-bold shadow-sm">{{ $users->count() }} Orang</span>
                        </div>
                        <div class="bg-white shadow-sm rounded-b-xl border border-t-0 border-gray-200 overflow-hidden">
                            <ul class="divide-y divide-gray-100">
                                @foreach ($users as $user)
                                    <li class="p-4 sm:p-5 hover:bg-blue-50/50 transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div class="flex items-start sm:items-center space-x-4">
                                                <div class="flex-shrink-0 h-10 w-10 md:h-12 md:w-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg md:text-xl">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-bold text-gray-900 truncate">
                                                        {{ $user->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 break-all">
                                                        {{ $user->email }}
                                                    </p>
                                                    <div class="mt-1 flex flex-wrap gap-2">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                                            <i class="fa-solid fa-circle text-[8px] mr-1 text-green-500"></i> Aktif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end space-x-2 mt-2 sm:mt-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <i class="fa-solid fa-pen mr-1.5 text-yellow-500"></i> Edit
                                                </a>
                                                
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus pegawai ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-red-200 shadow-sm text-xs font-medium rounded text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <i class="fa-solid fa-trash mr-1.5"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                            <i class="fa-regular fa-folder-open text-gray-400 text-xl"></i>
                        </div>
                        <p>Tidak ada pegawai internal aktif.</p>
                    </div>
                @endforelse
            </div>

            {{-- 3. MITRA (PPAT / FREELANCE) --}}
            <div>
                <div class="flex items-center mb-4 space-x-2">
                    <div class="w-1 h-6 bg-emerald-600 rounded"></div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Daftar Mitra Eksternal (PPAT / Freelance)</h3>
                </div>

                @forelse ($mitraUsers as $jabatan => $users)
                    <div class="mb-6">
                        <div class="bg-emerald-50 px-4 py-3 rounded-t-xl border border-emerald-200 flex justify-between items-center">
                            <h4 class="font-bold text-emerald-800 text-sm uppercase tracking-wide">
                                <i class="fa-solid fa-handshake mr-2 text-emerald-600"></i> {{ $jabatan }}
                            </h4>
                            <span class="bg-emerald-200 text-emerald-900 text-xs px-2.5 py-1 rounded-full font-bold shadow-sm">{{ $users->count() }} Mitra</span>
                        </div>
                        <div class="bg-white shadow-sm rounded-b-xl border border-t-0 border-gray-200 overflow-hidden">
                            <ul class="divide-y divide-gray-100">
                                @foreach ($users as $user)
                                    <li class="p-4 sm:p-5 hover:bg-emerald-50/30 transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div class="flex items-start sm:items-center space-x-4">
                                                <div class="flex-shrink-0 h-10 w-10 md:h-12 md:w-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold text-lg md:text-xl">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-bold text-gray-900 truncate">
                                                        {{ $user->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 break-all">
                                                        {{ $user->email }}
                                                    </p>
                                                    <div class="mt-1 flex flex-wrap gap-2">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                                            <i class="fa-solid fa-circle text-[8px] mr-1 text-green-500"></i> Aktif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end space-x-2 mt-2 sm:mt-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <i class="fa-solid fa-pen mr-1.5 text-yellow-500"></i> Edit
                                                </a>
                                                
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus mitra ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-red-200 shadow-sm text-xs font-medium rounded text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <i class="fa-solid fa-trash mr-1.5"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                            <i class="fa-regular fa-folder-open text-gray-400 text-xl"></i>
                        </div>
                        <p>Tidak ada Mitra / Eksternal aktif.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>