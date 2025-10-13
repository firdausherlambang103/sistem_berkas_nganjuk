<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fa-solid fa-file-word mr-2"></i>
            Buat Surat Tugas & Berita Acara
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('surat-tugas.generate') }}" method="POST" class="p-6 lg:p-8">
                    @csrf
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p class="font-bold">Error</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Kolom Kiri --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="berkas_id" value="Pilih Berkas Terkait" />
                                <select id="berkas_id" name="berkas_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih dari daftar berkas aktif --</option>
                                    @foreach ($berkasAktif as $berkas)
                                        <option value="{{ $berkas->id }}" {{ old('berkas_id') == $berkas->id ? 'selected' : '' }}>
                                            {{ $berkas->nomer_berkas }} - {{ $berkas->nama_pemohon }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('berkas_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="no_st" value="Nomor Surat Tugas" />
                                <x-text-input id="no_st" name="no_st" type="text" class="mt-1 block w-full" :value="old('no_st')" required />
                                <x-input-error :messages="$errors->get('no_st')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="tgl_mulai_pa" value="Tanggal Mulai" />
                                <x-text-input id="tgl_mulai_pa" name="tgl_mulai_pa" type="date" class="mt-1 block w-full" :value="old('tgl_mulai_pa')" required />
                                <x-input-error :messages="$errors->get('tgl_mulai_pa')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="selesai_pa" value="Tanggal Selesai" />
                                <x-text-input id="selesai_pa" name="selesai_pa" type="date" class="mt-1 block w-full" :value="old('selesai_pa')" required />
                                <x-input-error :messages="$errors->get('selesai_pa')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="di_305" value="DI 305 No." />
                                <x-text-input id="di_305" name="di_305" type="text" class="mt-1 block w-full" :value="old('di_305')" required />
                                <x-input-error :messages="$errors->get('di_305')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="thn_di_305" value="Tahun DI 305" />
                                <x-text-input id="thn_di_305" name="thn_di_305" type="text" class="mt-1 block w-full" :value="old('thn_di_305')" required />
                                <x-input-error :messages="$errors->get('thn_di_305')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tgl_peta_bidang" value="Tanggal Peta Bidang" />
                                <x-text-input id="tgl_peta_bidang" name="tgl_peta_bidang" type="date" class="mt-1 block w-full" :value="old('tgl_peta_bidang')" required />
                                <x-input-error :messages="$errors->get('tgl_peta_bidang')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="nib" value="NIB" />
                                <x-text-input id="nib" name="nib" type="text" class="mt-1 block w-full" :value="old('nib')" required />
                                <x-input-error :messages="$errors->get('nib')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="no_leter_c" value="No. Letter C" />
                                <x-text-input id="no_leter_c" name="no_leter_c" type="text" class="mt-1 block w-full" :value="old('no_leter_c')" required />
                                <x-input-error :messages="$errors->get('no_leter_c')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="persil" value="Persil" />
                                <x-text-input id="persil" name="persil" type="text" class="mt-1 block w-full" :value="old('persil')" required />
                                <x-input-error :messages="$errors->get('persil')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="klas" value="Klas" />
                                <x-text-input id="klas" name="klas" type="text" class="mt-1 block w-full" :value="old('klas')" required />
                                <x-input-error :messages="$errors->get('klas')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Kolom Kanan --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="nama_letter_c" value="Nama di Letter C" />
                                <x-text-input id="nama_letter_c" name="nama_letter_c" type="text" class="mt-1 block w-full" :value="old('nama_letter_c')" required />
                                <x-input-error :messages="$errors->get('nama_letter_c')" class="mt-2" />
                            </div>
                           
                            <div>
                                <x-input-label for="sporadik" value="Tanggal Sporadik" />
                                <x-text-input id="sporadik" name="sporadik" type="date" class="mt-1 block w-full" :value="old('sporadik')" required />
                                <x-input-error :messages="$errors->get('sporadik')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="tgl_skrt" value="Tanggal SKRT" />
                                <x-text-input id="tgl_skrt" name="tgl_skrt" type="date" class="mt-1 block w-full" :value="old('tgl_skrt')" required />
                                <x-input-error :messages="$errors->get('tgl_skrt')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="no_skrt" value="Nomor SKRT" />
                                <x-text-input id="no_skrt" name="no_skrt" type="text" class="mt-1 block w-full" :value="old('no_skrt')" required />
                                <x-input-error :messages="$errors->get('no_skrt')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="bak_tanggal" value="Tanggal BAK" />
                                <x-text-input id="bak_tanggal" name="bak_tanggal" type="date" class="mt-1 block w-full" :value="old('bak_tanggal')" required />
                                <x-input-error :messages="$errors->get('bak_tanggal')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="bak_nomor" value="Nomor BAK" />
                                <x-text-input id="bak_nomor" name="bak_nomor" type="text" class="mt-1 block w-full" :value="old('bak_nomor')" required />
                                <x-input-error :messages="$errors->get('bak_nomor')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="utara" value="Batas Utara" />
                                <x-text-input id="utara" name="utara" type="text" class="mt-1 block w-full" :value="old('utara')" required />
                                <x-input-error :messages="$errors->get('utara')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="timur" value="Batas Timur" />
                                <x-text-input id="timur" name="timur" type="text" class="mt-1 block w-full" :value="old('timur')" required />
                                <x-input-error :messages="$errors->get('timur')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="selatan" value="Batas Selatan" />
                                <x-text-input id="selatan" name="selatan" type="text" class="mt-1 block w-full" :value="old('selatan')" required />
                                <x-input-error :messages="$errors->get('selatan')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="barat" value="Batas Barat" />
                                <x-text-input id="barat" name="barat" type="text" class="mt-1 block w-full" :value="old('barat')" required />
                                <x-input-error :messages="$errors->get('barat')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-end mt-8 border-t pt-6 space-x-4">
                        <button type="submit" name="template_type" value="st" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fa-solid fa-download mr-2"></i>
                            Buat Surat Tugas (ST)
                        </button>
                        <button type="submit" name="template_type" value="ba" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <i class="fa-solid fa-download mr-2"></i>
                            Buat Berita Acara (BA)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>