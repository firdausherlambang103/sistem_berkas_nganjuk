<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                        {{ __('Laporan Rinci') }}
                    </x-nav-link>
                    <x-nav-link :href="route('ruang-kerja')" :active="request()->routeIs('ruang-kerja')">
                        {{ __('Ruang Kerja') }}
                    </x-nav-link>
                        {{-- MENU BARU --}}
                    <x-nav-link :href="route('surat-tugas.create')" :active="request()->routeIs('surat-tugas.create')">
                        {{ __('Surat Tugas & BA') }}
                    </x-nav-link>

                    {{-- Penjadwalan Ukur diubah menjadi Dropdown --}}
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
                                    {{-- [BARU] Link Template WA --}}
                                    <x-dropdown-link :href="route('admin.wa-templates.index')">
                                        {{ __('Template WA') }}
                                    </x-dropdown-link>
                                    {{-- [BARU] Menu Riwayat WA --}}
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
             <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')">
                {{ __('Laporan Rinci') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ruang-kerja')" :active="request()->routeIs('ruang-kerja')">
                {{ __('Ruang Kerja') }}
            </x-responsive-nav-link>
            {{-- Tambahkan link responsive untuk penjadwalan jika diperlukan --}}
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

                @if(Auth::user()->jabatan && Auth::user()->jabatan->is_admin)
                    <div class="border-t border-gray-200 mt-3 pt-3">
                        <div class="px-4 font-medium text-base text-gray-800">Administrasi</div>
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
                            <x-responsive-nav-link :href="route('admin.permohonan.index')">
                                {{ __('Manajemen Jenis Permohonan') }}
                            </x-responsive-nav-link>
                            {{-- [BARU] Link Template WA Responsive --}}
                            <x-responsive-nav-link :href="route('admin.wa-templates.index')">
                                {{ __('Template WA') }}
                            </x-responsive-nav-link>
                            {{-- [BARU] Menu Riwayat WA Responsive --}}
                            <x-responsive-nav-link :href="route('admin.wa-logs.index')" :active="request()->routeIs('admin.wa-logs.*')">
                                {{ __('Riwayat WA') }}
                            </x-responsive-nav-link>
                            <x-dropdown-link :href="route('admin.whatsapp.scan')">
                                {{ __('Scan WhatsApp') }}
                            </x-dropdown-link>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>