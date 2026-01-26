<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaPlaceholder;
use Illuminate\Support\Facades\DB;

class WaPlaceholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Bersihkan tabel sebelum mengisi data baru (agar tidak duplikat)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        WaPlaceholder::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $placeholders = [
            // --- IDENTITAS PEMOHON & BERKAS ---
            [
                'placeholder' => '{nama_pemohon}',
                'deskripsi'   => 'nama_pemohon', // Kolom langsung di tabel berkas
            ],
            [
                'placeholder' => '{nomer_berkas}',
                'deskripsi'   => 'nomer_berkas',
            ],
            [
                'placeholder' => '{tahun}',
                'deskripsi'   => 'tahun',
            ],
            [
                'placeholder' => '{status_berkas}',
                'deskripsi'   => 'status', // Status pengerjaan (Baru, Ukur, Selesai, dll)
            ],
            [
                'placeholder' => '{tgl_masuk}',
                'deskripsi'   => 'waktu_mulai_proses', // Akan diformat tanggal otomatis oleh WaService
            ],

            // --- DATA WILAYAH (Smart Fallback) ---
            // WaService akan mencari relasi dulu, jika null akan ambil string dari kolom desa/kecamatan
            [
                'placeholder' => '{nama_desa}',
                'deskripsi'   => 'desa.nama_desa', 
            ],
            [
                'placeholder' => '{nama_kecamatan}',
                'deskripsi'   => 'kecamatan.nama_kecamatan', 
            ],

            // --- DATA HAK TANAH ---
            [
                'placeholder' => '{nomer_hak}',
                'deskripsi'   => 'nomer_hak',
            ],
            [
                'placeholder' => '{jenis_hak}',
                'deskripsi'   => 'jenis_alas_hak', // Contoh: Hak Milik, HGB
            ],
            [
                'placeholder' => '{luas_tanah}',
                'deskripsi'   => 'luas_tanah',
            ],

            // --- RELASI KEGIATAN & PETUGAS ---
            [
                'placeholder' => '{jenis_permohonan}',
                'deskripsi'   => 'jenisPermohonan.nama_permohonan', // Mengambil nama kegiatan
            ],
            [
                'placeholder' => '{posisi_sekarang}',
                'deskripsi'   => 'posisiSekarang.name', // Nama User/Petugas yang sedang memegang berkas
            ],
            [
                'placeholder' => '{petugas_ukur}',
                'deskripsi'   => 'petugasUkur.nama', // Nama Petugas Ukur (jika ada)
            ],
            [
                'placeholder' => '{nama_kuasa}',
                'deskripsi'   => 'penerimaKuasa.nama_kuasa', // Nama Penerima Kuasa (jika ada)
            ],
            
            // --- INFO TAMBAHAN ---
            [
                'placeholder' => '{keterangan}',
                'deskripsi'   => 'keterangan', // Catatan tambahan pada berkas
            ],
        ];

        // Loop insert data
        foreach ($placeholders as $data) {
            WaPlaceholder::create($data);
        }
    }
}