<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Template WA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Form Edit --}}
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form action="{{ route('admin.wa-templates.update', $waTemplate->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <x-input-label for="nama_template" :value="__('Nama Template')" />
                            <x-text-input id="nama_template" class="block mt-1 w-full" type="text" name="nama_template" :value="old('nama_template', $waTemplate->nama_template)" required placeholder="Misal: BERKAS_SELESAI" />
                            <x-input-error :messages="$errors->get('nama_template')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="isi_pesan" :value="__('Isi Pesan')" />
                            <textarea id="isi_pesan" name="isi_pesan" rows="8" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('isi_pesan', $waTemplate->isi_pesan) }}</textarea>
                            <x-input-error :messages="$errors->get('isi_pesan')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.wa-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase hover:bg-gray-300 transition">
                                Batal
                            </a>
                            <x-primary-button>Perbarui Template</x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- Helper Placeholders --}}
                <div class="bg-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-indigo-100 h-fit">
                    <h3 class="font-bold text-indigo-800 mb-3"><i class="fa-solid fa-circle-info mr-1"></i> Gunakan Placeholder</h3>
                    <p class="text-sm text-gray-600 mb-4">Klik kode di bawah untuk menyalin dan tempel ke dalam isi pesan.</p>
                    
                    <div class="flex flex-wrap gap-2">
                        @foreach($placeholders as $ph)
                            <button type="button" onclick="copyToClipboard('{{ $ph->placeholder }}')" class="px-2 py-1 bg-white border border-indigo-200 rounded text-xs font-mono font-bold text-indigo-600 hover:bg-indigo-100 transition" title="Klik untuk menyalin">
                                {{ $ph->placeholder }}
                            </button>
                        @endforeach
                    </div>

                    <div id="copy-feedback" class="hidden mt-3 text-xs text-green-600 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-check"></i> Kode disalin!
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Script Copy --}}
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const feedback = document.getElementById('copy-feedback');
                feedback.classList.remove('hidden');
                setTimeout(() => {
                    feedback.classList.add('hidden');
                }, 2000);
                
                // Insert at cursor position
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