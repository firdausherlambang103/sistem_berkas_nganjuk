@php
    // Cek apakah user yang login adalah Mitra (PPAT / Freelance)
    $isMitra = Auth::user()->jabatan && in_array(Auth::user()->jabatan->nama_jabatan, ['PPAT', 'Freelance']);
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ $isMitra ? route('mitra.dashboard') : route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                    @if($isMitra)
                        {{-- ========================================== --}}
                        {{-- MENU KHUSUS MITRA (PPAT & FREELANCE)       --}}
                        {{-- ========================================== --}}
                        <x-nav-link :href="route('mitra.dashboard')" :active="request()->routeIs('mitra.dashboard')">
                            {{ __('Dashboard Mitra') }}
                        </x-nav-link>

                        <x-nav-link :href="route('mitra.ruang-kerja')" :active="request()->routeIs('mitra.ruang-kerja')">
                            {{ __('Ruang Kerja Mitra') }}
                        </x-nav-link>

                    @else
                        {{-- ========================================== --}}
                        {{-- MENU INTERNAL (PEGAWAI & ADMIN)            --}}
                        {{-- ========================================== --}}
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        @if(Auth::user()->hasMenuAccess('laporan_rinci'))
                            <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                                {{ __('Laporan Rinci') }}
                            </x-nav-link>
                        @endif

                        @if(Auth::user()->hasMenuAccess('ruang_kerja'))
                            <x-nav-link :href="route('ruang-kerja')" :active="request()->routeIs('ruang-kerja')">
                                {{ __('Ruang Kerja') }}
                            </x-nav-link>
                        @endif
                        
                        @if(Auth::user()->hasMenuAccess('silabus') || (Auth::user()->jabatan && Auth::user()->jabatan->nama_jabatan === 'Petugas Buku Tanah'))
                            <x-nav-link :href="route('peminjaman-bt.index')" :active="request()->routeIs('peminjaman-bt.*')">
                                {{ __('Silabus') }}
                            </x-nav-link>
                        @endif

                        {{-- Penjadwalan Ukur Dropdown --}}
                        @if(Auth::user()->hasMenuAccess('penjadwalan_ukur'))
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <x-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('jadwal-ukur.*') ? 'border-indigo-400' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>Penjadwalan Ukur</div>
                                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('jadwal-ukur.index')">
                                            {{ __('Buat Jadwal Baru') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('jadwal-ukur.dashboard-petugas')">
                                            {{ __('Dashboard Petugas') }}
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif

                        {{-- Admin Dropdown --}}
                        @if(Auth::user()->jabatan && Auth::user()->jabatan->is_admin)
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            <div>Administrasi</div>
                                            <div class="ms-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="block px-4 py-2 text-xs text-gray-400">Master Data</div>
                                        <x-dropdown-link :href="route('admin.users.index')">
                                            {{ __('User Approval') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.jabatan.index')">
                                            {{ __('Manajemen Jabatan') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.petugas-ukur.index')">
                                            {{ __('Manajemen Petugas Ukur') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.setting-area-kerja.index')">
                                            {{ __('Setting Area Kerja') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.kecamatan.index')">
                                            {{ __('Manajemen Kecamatan') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.desa.index')">
                                            {{ __('Manajemen Desa') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.kuasa.index')">
                                            {{ __('Manajemen Penerima Kuasa') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.permohonan.index')">
                                            {{ __('Manajemen Jenis Permohonan') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.status.index')">
                                            {{ __('Manajemen Status') }}
                                        </x-dropdown-link>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <div class="block px-4 py-2 text-xs text-gray-400">Alat Bantu</div>
                                        
                                        <x-dropdown-link :href="route('admin.perbaikan.index')">
                                            {{ __('Perbaikan Posisi Berkas') }}
                                        </x-dropdown-link>

                                        <div class="border-t border-gray-100 my-1"></div>
                                        <div class="block px-4 py-2 text-xs text-gray-400">WhatsApp Gateway</div>

                                        <x-dropdown-link :href="route('admin.wa-placeholders.index')">
                                            {{ __('Placeholder WA') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.wa-templates.index')">
                                            {{ __('Template WA') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.wa-logs.index')">
                                            {{ __('Riwayat WA') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('admin.whatsapp.scan')">
                                            {{ __('Scan WhatsApp') }}
                                        </x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            
            @if($isMitra)
                {{-- MENU KHUSUS MITRA MOBILE --}}
                <x-responsive-nav-link :href="route('mitra.dashboard')" :active="request()->routeIs('mitra.dashboard')">
                    {{ __('Dashboard Mitra') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('mitra.ruang-kerja')" :active="request()->routeIs('mitra.ruang-kerja')">
                    {{ __('Ruang Kerja Mitra') }}
                </x-responsive-nav-link>
            @else
                {{-- MENU INTERNAL MOBILE --}}
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                @if(Auth::user()->hasMenuAccess('laporan_rinci'))
                    <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        {{ __('Laporan Rinci') }}
                    </x-responsive-nav-link>
                @endif

                @if(Auth::user()->hasMenuAccess('ruang_kerja'))
                    <x-responsive-nav-link :href="route('ruang-kerja')" :active="request()->routeIs('ruang-kerja')">
                        {{ __('Ruang Kerja') }}
                    </x-responsive-nav-link>
                @endif
                
                @if(Auth::user()->hasMenuAccess('silabus') || (Auth::user()->jabatan && Auth::user()->jabatan->nama_jabatan === 'Petugas Buku Tanah'))
                    <x-responsive-nav-link :href="route('peminjaman-bt.index')" :active="request()->routeIs('peminjaman-bt.*')">
                        {{ __('Silabus') }}
                    </x-responsive-nav-link>
                @endif

                @if(Auth::user()->hasMenuAccess('penjadwalan_ukur'))
                    <x-responsive-nav-link :href="route('jadwal-ukur.index')" :active="request()->routeIs('jadwal-ukur.index')">
                        {{ __('Buat Jadwal Baru') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('jadwal-ukur.dashboard-petugas')" :active="request()->routeIs('jadwal-ukur.dashboard-petugas')">
                        {{ __('Dashboard Petugas') }}
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                {{-- Responsive Admin Menu (Hanya muncul jika admin & bukan Mitra) --}}
                @if(!$isMitra && Auth::user()->jabatan && Auth::user()->jabatan->is_admin)
                    <div class="border-t border-gray-200 mt-3 pt-3">
                        <div class="px-4 font-medium text-base text-gray-800 bg-gray-50 py-2">Administrasi</div>
                         <div class="mt-1 space-y-1">
                            <x-responsive-nav-link :href="route('admin.users.index')">
                                {{ __('User Approval') }}
                            </x-responsive-nav-link>
                             <x-responsive-nav-link :href="route('admin.jabatan.index')">
                                {{ __('Manajemen Jabatan') }}
                            </x-responsive-nav-link>
                             <x-responsive-nav-link :href="route('admin.petugas-ukur.index')">
                                {{ __('Manajemen Petugas Ukur') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.setting-area-kerja.index')">
                                {{ __('Setting Area Kerja') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.kecamatan.index')">
                                {{ __('Manajemen Kecamatan') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.desa.index')">
                                {{ __('Manajemen Desa') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.kuasa.index')">
                                {{ __('Manajemen Penerima Kuasa') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.permohonan.index')">
                                {{ __('Manajemen Jenis Permohonan') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.status.index')">
                                {{ __('Manajemen Status') }}
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.perbaikan.index')" :active="request()->routeIs('admin.perbaikan.*')">
                                {{ __('Perbaikan Posisi Berkas') }}
                            </x-responsive-nav-link>

                            <x-responsive-nav-link :href="route('admin.wa-placeholders.index')" :active="request()->routeIs('admin.wa-placeholders.*')">
                                {{ __('Placeholder WA') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.wa-templates.index')">
                                {{ __('Template WA') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.wa-logs.index')" :active="request()->routeIs('admin.wa-logs.*')">
                                {{ __('Riwayat WA') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.whatsapp.scan')">
                                {{ __('Scan WhatsApp') }}
                            </x-responsive-nav-link>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>