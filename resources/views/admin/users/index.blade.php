<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-users-gear text-indigo-600 mr-2"></i> {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    {{-- KITA MENGGUNAKAN ALPINE.JS UNTUK FITUR TABS --}}
    <div class="py-8 bg-gray-50 min-h-screen" x-data="{ tab: 'internal' }">
        {{-- Menggunakan w-full agar layout mengambil layar penuh dengan margin yang pas --}}
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm flex items-center" role="alert">
                    <i class="fa-solid fa-circle-check mr-2"></i>
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- HEADER AKSI, PENCARIAN, & TABS --}}
            <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4">

                {{-- TOMBOL TABS --}}
                <div class="flex bg-gray-100 p-1 rounded-lg w-full md:w-auto">
                    <button @click="tab = 'internal'"
                            :class="tab === 'internal' ? 'bg-white shadow-sm text-indigo-700 font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'"
                            class="flex-1 md:flex-none px-4 md:px-6 py-2 rounded-md text-sm transition-all flex items-center justify-center whitespace-nowrap">
                        <i class="fa-solid fa-building-user mr-2"></i> Internal BPN
                        <span class="ml-2 bg-indigo-100 text-indigo-600 py-0.5 px-2 rounded-full text-[10px]">{{ $internalUsers->count() }}</span>
                    </button>

                    <button @click="tab = 'mitra'"
                            :class="tab === 'mitra' ? 'bg-white shadow-sm text-teal-700 font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'"
                            class="flex-1 md:flex-none px-4 md:px-6 py-2 rounded-md text-sm transition-all flex items-center justify-center whitespace-nowrap">
                        <i class="fa-solid fa-handshake mr-2"></i> Mitra Eksternal
                        <span class="ml-2 bg-teal-100 text-teal-600 py-0.5 px-2 rounded-full text-[10px]">{{ $mitraUsers->count() }}</span>
                    </button>
                </div>

                {{-- PENCARIAN --}}
                <form action="{{ route('admin.users.index') }}" method="GET" class="relative w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="pl-10 pr-8 py-2 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400"></i>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-red-500 transition" title="Reset Pencarian">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </form>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB KONTEN 1: PENGGUNA INTERNAL (NON MITRA) --}}
            {{-- ======================================================== --}}
            <div x-show="tab === 'internal'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-indigo-800 uppercase tracking-wider w-10">No</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Profil Pengguna</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Jabatan</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-bold text-indigo-800 uppercase tracking-wider w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($internalUsers as $index => $user)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-bold text-gray-900 leading-tight">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500 break-all">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 inline-flex text-[11px] leading-tight font-semibold rounded bg-indigo-50 text-indigo-700 border border-indigo-100 whitespace-normal break-words max-w-[200px]">
                                                {{ optional($user->jabatan)->nama_jabatan ?? 'Belum Diatur' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($user->is_approved)
                                                <span class="text-green-600 font-bold text-[11px] flex items-center"><i class="fa-solid fa-circle-check mr-1.5"></i> Aktif</span>
                                            @else
                                                <span class="text-red-500 font-bold text-[11px] flex items-center"><i class="fa-solid fa-clock mr-1.5"></i> Menunggu</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded flex items-center justify-center transition border border-indigo-200 hover:border-indigo-600 shadow-sm" title="Edit Pengguna">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus permanen pengguna ini?');" class="inline-block m-0 p-0">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded flex items-center justify-center transition border border-red-200 hover:border-red-600 shadow-sm" title="Hapus Pengguna">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                                            <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300 block"></i>
                                            @if(request('search')) Pencarian "<b>{{ request('search') }}</b>" tidak ditemukan. @else Belum ada data pengguna internal. @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB KONTEN 2: MITRA EKSTERNAL --}}
            {{-- ======================================================== --}}
            <div x-show="tab === 'mitra'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-teal-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-teal-800 uppercase tracking-wider w-10">No</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-teal-800 uppercase tracking-wider">Profil Mitra</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-teal-800 uppercase tracking-wider">Tipe Mitra</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-teal-800 uppercase tracking-wider">Status Approval</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-bold text-teal-800 uppercase tracking-wider w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($mitraUsers as $index => $user)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-9 w-9 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 font-bold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-bold text-gray-900 leading-tight">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500 break-all">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 inline-flex text-[11px] leading-tight font-semibold rounded bg-teal-50 text-teal-700 border border-teal-100 whitespace-normal break-words max-w-[200px]">
                                                {{ optional($user->jabatan)->nama_jabatan ?? 'Mitra' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($user->is_approved)
                                                <span class="text-green-600 font-bold text-[11px] flex items-center"><i class="fa-solid fa-circle-check mr-1.5"></i> Disetujui</span>
                                            @else
                                                <span class="text-red-500 font-bold text-[11px] flex items-center"><i class="fa-solid fa-clock mr-1.5"></i> Menunggu Approval</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white rounded flex items-center justify-center transition border border-teal-200 hover:border-teal-600 shadow-sm" title="Edit Mitra">
                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus permanen mitra ini?');" class="inline-block m-0 p-0">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-8 h-8 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded flex items-center justify-center transition border border-red-200 hover:border-red-600 shadow-sm" title="Hapus Mitra">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                                            <i class="fa-solid fa-handshake-slash text-4xl mb-3 text-gray-300 block"></i>
                                            @if(request('search')) Pencarian "<b>{{ request('search') }}</b>" tidak ditemukan. @else Belum ada data mitra terdaftar. @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    @push('scripts')
    <style>
        /* Desain scrollbar agar mulus jika diakses via layar kecil (HP) */
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @endpush
</x-app-layout>