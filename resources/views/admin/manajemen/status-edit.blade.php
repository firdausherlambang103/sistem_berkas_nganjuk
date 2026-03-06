<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Master Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('admin.status.update', $status->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="nama_status" value="Nama Status" />
                            <x-text-input id="nama_status" name="nama_status" type="text" class="mt-1 block w-full" value="{{ old('nama_status', $status->nama_status) }}" required />
                            <x-input-error :messages="$errors->get('nama_status')" class="mt-2" />
                        </div>
                        
                        <div>
                            <label for="butuh_waktu_hari" class="inline-flex items-center">
                                <input id="butuh_waktu_hari" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="butuh_waktu_hari" value="1" {{ $status->butuh_waktu_hari ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 font-bold">Membutuhkan input form 'Berapa Hari' di Ruang Kerja?</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.status.index') }}" class="mr-4 text-sm text-gray-600 hover:text-gray-900">Batal</a>
                        <x-primary-button>Update Data</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>