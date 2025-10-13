<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisPermohonan;
use Illuminate\Support\Facades\DB;

class JenisPermohonanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CATATAN: Perintah delete() atau truncate() telah dihapus untuk keamanan data.

        $data = [
            // Contoh yang TIDAK memerlukan ukur
            ['nama_permohonan' => 'Blokir', 'waktu_timeline_hari' => 3, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Ganti Nama', 'waktu_timeline_hari' => 7, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Hak Tanggungan', 'waktu_timeline_hari' => 7, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Pengecekan Sertipikat', 'waktu_timeline_hari' => 1, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Peralihan Hak - Jual Beli', 'waktu_timeline_hari' => 7, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Roya', 'waktu_timeline_hari' => 5, 'memerlukan_ukur' => false],
            ['nama_permohonan' => 'Sertipikat Pengganti Karena Rusak', 'waktu_timeline_hari' => 14, 'memerlukan_ukur' => false],
            
            // Contoh yang MEMERLUKAN ukur
            ['nama_permohonan' => 'Pemecahan Bidang', 'waktu_timeline_hari' => 30, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pemisahan Bidang', 'waktu_timeline_hari' => 30, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Penataan Batas', 'waktu_timeline_hari' => 21, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pengembalian Batas', 'waktu_timeline_hari' => 21, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Penggabungan Bidang', 'waktu_timeline_hari' => 30, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pengukuran Dan Pemetaan Kadastral', 'waktu_timeline_hari' => 30, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pengukuran Ulang Dan Pemetaan Kadastral', 'waktu_timeline_hari' => 30, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pengukuran Untuk Mengetahui Luas', 'waktu_timeline_hari' => 14, 'memerlukan_ukur' => true],
            ['nama_permohonan' => 'Pendaftaran Tanah Pertama Kali Pemberian Hak', 'waktu_timeline_hari' => 90, 'memerlukan_ukur' => true],
        ];

        // --- PERUBAHAN UTAMA DI SINI ---
        // Menggunakan updateOrInsert untuk keamanan data.
        foreach ($data as $item) {
            JenisPermohonan::updateOrInsert(
                ['nama_permohonan' => $item['nama_permohonan']], // Kunci untuk mencari data yang sudah ada
                [                                               // Data untuk diupdate atau dibuat
                    'waktu_timeline_hari' => $item['waktu_timeline_hari'],
                    'memerlukan_ukur' => $item['memerlukan_ukur'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

