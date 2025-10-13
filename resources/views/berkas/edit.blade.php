<!-- File: resources/views/berkas/edit.blade.php -->

<!--
    CATATAN:
    File ini berisi struktur form lengkap berdasarkan screenshot Anda.
    Pastikan Anda sudah mengirimkan variabel `$berkas`, `$kecamatans`, dan `$jenisPermohonans`
    dari controller ke view ini.
-->

@extends('layouts.app') <!-- Ganti dengan layout utama Anda jika berbeda -->

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Edit Berkas: {{ $berkas->nomer_berkas }}</h4>
        </div>
        <div class="card-body">

            <!--
                ================================================================
                FORM UPDATE BERKAS
                ================================================================
                - action: Mengarah ke route 'berkas.update' dengan ID berkas.
                - method="POST": Form HTML hanya bisa POST atau GET.
                - @csrf: Wajib untuk keamanan dari Laravel.
                - @method('PATCH'): INI BAGIAN TERPENTING. Ini "membohongi" browser
                  agar Laravel mengenali request ini sebagai PATCH, sesuai
                  dengan yang dibutuhkan oleh route resource untuk update.
                  Inilah yang memperbaiki error "The PUT method is not supported".
            -->
            <form method="POST" action="{{ route('berkas.update', $berkas->id) }}">
                @csrf
                @method('PATCH')

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nomer_berkas" class="form-label">Nomer Berkas</label>
                            <input type="text" class="form-control" id="nomer_berkas" name="nomer_berkas" value="{{ old('nomer_berkas', $berkas->nomer_berkas) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_pemohon" class="form-label">Nama Pemohon / Kuasa</label>
                            <input type="text" class="form-control" id="nama_pemohon" name="nama_pemohon" value="{{ old('nama_pemohon', $berkas->nama_pemohon) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis_alas_hak" class="form-label">Jenis Alas Hak</label>
                            <input type="text" class="form-control" id="jenis_alas_hak" name="jenis_alas_hak" value="{{ old('jenis_alas_hak', $berkas->jenis_alas_hak) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="nomer_hak" class="form-label">Nomer Hak</label>
                            <input type="text" class="form-control" id="nomer_hak" name="nomer_hak" value="{{ old('nomer_hak', $berkas->nomer_hak) }}" required>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kecamatan" class="form-label">Kecamatan</label>
                            <select class="form-select" id="kecamatan" name="kecamatan" required>
                                <option value="">Pilih Kecamatan</option>
                                @foreach($kecamatans as $kec)
                                    <option value="{{ $kec->nama_kecamatan }}" {{ old('kecamatan', $berkas->kecamatan) == $kec->nama_kecamatan ? 'selected' : '' }}>
                                        {{ $kec->nama_kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="desa" class="form-label">Desa</label>
                            <input type="text" class="form-control" id="desa" name="desa" value="{{ old('desa', $berkas->desa) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="nomer_wa" class="form-label">Nomor WhatsApp (Opsional)</label>
                            <input type="text" class="form-control" id="nomer_wa" name="nomer_wa" value="{{ old('nomer_wa', $berkas->nomer_wa) }}">
                        </div>
                        <div class="mb-3">
                            <label for="jenis_permohonan_id" class="form-label">Jenis Permohonan</label>
                            <select class="form-select" id="jenis_permohonan_id" name="jenis_permohonan_id" required>
                                <option value="">Pilih Jenis Permohonan</option>
                                @foreach($jenisPermohonans as $jenis)
                                    <option value="{{ $jenis->id }}" {{ old('jenis_permohonan_id', $berkas->jenis_permohonan_id) == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama_permohonan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Catatan (Full Width) -->
                <div class="mb-3">
                    <label for="catatan" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ old('catatan', $berkas->catatan) }}</textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

