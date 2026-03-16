<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Penerima Kuasa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Pesan Error Validasi --}}
                    @if($errors->any())
                        <div class="mb-4 p-4 text-red-700 bg-red-100 rounded-lg">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="max-w-3xl p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Data: {{ $kuasa->nama_kuasa }}</h3>
                        
                        <form action="{{ route('admin.kuasa.update', $kuasa->id) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Kuasa</label>
                                <input type="text" name="kode_kuasa" value="{{ old('kode_kuasa', $kuasa->kode_kuasa) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Kuasa</label>
                                <input type="text" name="nama_kuasa" value="{{ old('nama_kuasa', $kuasa->nama_kuasa) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                                <input type="text" name="nomer_wa" value="{{ old('nomer_wa', $kuasa->nomer_wa) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div class="flex items-center gap-4 mt-6">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                    <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.kuasa.index') }}" class="text-gray-600 hover:underline">Batal</a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>