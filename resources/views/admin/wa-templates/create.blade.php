<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-plus-circle mr-2 text-indigo-500"></i> {{ __('Buat Template WA Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Kolom Kiri: Form Input --}}
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form action="{{ route('admin.wa-templates.store') }}" method="POST">
                        @csrf
                        
                        {{-- Nama Template --}}
                        <div class="mb-4">
                            <x-input-label for="nama_template" :value="__('Nama Template')" />
                            {{-- Input Name: nama_template (sesuai Controller) --}}
                            <x-text-input id="nama_template" class="block mt-1 w-full" type="text" name="nama_template" :value="old('nama_template')" required autofocus placeholder="Contoh: BERKAS_DITERIMA" />
                            <p class="text-xs text-gray-500 mt-1">Gunakan huruf kapital dan garis bawah (underscore) untuk nama template.</p>
                            <x-input-error :messages="$errors->get('nama_template')" class="mt-2" />
                        </div>

                        {{-- Isi Pesan --}}
                        <div class="mb-4">
                            <x-input-label for="isi_pesan" :value="__('Isi Pesan WhatsApp')" />
                            {{-- Input Name: isi_pesan (sesuai Controller) --}}
                            <textarea id="isi_pesan" name="isi_pesan" rows="8" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Halo [NAMA_PEMOHON], berkas Anda dengan nomor [NOMOR_BERKAS] sudah kami terima.">{{ old('isi_pesan') }}</textarea>
                            <x-input-error :messages="$errors->get('isi_pesan')" class="mt-2" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex justify-end gap-3 mt-6">
                            <a href="{{ route('admin.wa-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Template') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- Kolom Kanan: Helper Placeholder --}}
                <div class="md:col-span-1">
                    <div class="bg-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-indigo-100 sticky top-4">
                        <h3 class="font-bold text-indigo-800 mb-2 flex items-center">
                            <i class="fa-solid fa-tags mr-2"></i> Placeholder Tersedia
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Klik tombol di bawah untuk menyalin kode variabel (otomatis diganti data asli saat dikirim).</p>
                        
                        <div class="flex flex-wrap gap-2">
                            @if(isset($placeholders) && count($placeholders) > 0)
                                @foreach($placeholders as $ph)
                                    <button type="button" onclick="copyToClipboard('{{ $ph->placeholder }}')" class="px-2 py-1 bg-white border border-indigo-200 rounded text-xs font-mono font-bold text-indigo-600 hover:bg-indigo-600 hover:text-white transition duration-200 shadow-sm" title="Salin {{ $ph->placeholder }}">
                                        {{ $ph->placeholder }}
                                    </button>
                                @endforeach
                            @else
                                <p class="text-xs text-gray-400 italic">Belum ada placeholder.</p>
                            @endif
                        </div>

                        {{-- Feedback Copy --}}
                        <div id="copy-feedback" class="hidden mt-3 p-2 bg-green-100 text-green-700 text-xs font-bold rounded text-center transition-opacity duration-500">
                            <i class="fa-solid fa-check-circle mr-1"></i> Berhasil disalin!
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Script untuk Copy Paste --}}
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const feedback = document.getElementById('copy-feedback');
                feedback.classList.remove('hidden');
                feedback.classList.add('block');
                
                setTimeout(() => {
                    feedback.classList.add('hidden');
                    feedback.classList.remove('block');
                }, 2000);
                
                const textarea = document.getElementById('isi_pesan');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                
                textarea.value = value.substring(0, start) + text + value.substring(end);
                textarea.focus();
                textarea.selectionEnd = start + text.length;
            });
        }
    </script>
</x-app-layout>