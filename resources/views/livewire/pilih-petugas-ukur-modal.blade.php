<div>
    @if ($showModal)
        <div 
            class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 transition-opacity" 
            aria-hidden="true"
            wire:click="closeModal"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div 
                class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col"
                @click.away="$wire.closeModal()"
            >
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fa-solid fa-user-check mr-2"></i>
                        Pilih Petugas Ukur
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="p-4 border-b border-gray-200">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama petugas..." 
                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                    >
                </div>

                <!-- Petugas List -->
                <div class="p-4 overflow-y-auto flex-grow bg-gray-50">
                    <div wire:loading class="text-center p-6 text-gray-500">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Memuat...
                    </div>
                    
                    <div wire:loading.remove class="space-y-3">
                        @forelse ($semuaPetugas as $petugas)
                            @php
                                // Cek apakah petugas ini direkomendasikan berdasarkan kecamatan yang dipilih
                                $isRecommended = $kecamatanId && $petugas->areaKerja->contains('id', $kecamatanId);
                            @endphp
                            <div class="bg-white border {{ $isRecommended ? 'border-green-500 shadow-md' : 'border-gray-200' }} rounded-lg flex flex-col sm:flex-row items-center justify-between p-4 relative">
                                
                                @if($isRecommended)
                                    <span class="absolute -top-2 -right-2 px-2 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                                        <i class="fa-solid fa-star text-xs"></i> Direkomendasikan
                                    </span>
                                @endif

                                <!-- Petugas Info -->
                                <div class="flex-grow mb-4 sm:mb-0">
                                    <h4 class="text-md font-bold text-gray-900">{{ $petugas->user->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ optional($petugas->user->jabatan)->nama_jabatan ?? 'N/A' }}</p>
                                    <div class="mt-2 text-xs text-gray-600">
                                        <strong class="font-semibold">Area Kerja:</strong> 
                                        <span class="italic">
                                            @forelse($petugas->areaKerja as $kecamatan)
                                                {{ $kecamatan->nama_kecamatan }}{{ !$loop->last ? ', ' : '' }}
                                            @empty
                                                Belum diatur
                                            @endforelse
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Beban Kerja & Tombol Aksi -->
                                <div class="flex items-center space-x-4 flex-shrink-0">
                                    <div class="text-center">
                                        <p class="text-xl font-bold text-gray-800">{{ $petugas->jadwal_ukur_count }}</p>
                                        <p class="text-xs text-gray-500">Beban Berkas</p>
                                    </div>
                                    <button 
                                        wire:click="pilihPetugas({{ $petugas->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700"
                                    >
                                        Pilih Petugas
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-8 text-gray-500">
                                <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
                                <p>Tidak ada petugas yang cocok dengan pencarian Anda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

