<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WaPlaceholder;

class WaPlaceholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar placeholder berdasarkan kolom di tabel 'berkas' dan relasinya
        $placeholders = [
            // --- DATA UTAMA BERKAS ---
            [
                'placeholder' => '[NOMOR_BERKAS]',
                'deskripsi' => 'Nomor urut berkas',
            ],
            [
                'placeholder' => '[TAHUN_BERKAS]',
                'deskripsi' => 'Tahun pendaftaran berkas',
            ],
            [
                'placeholder' => '[STATUS_BERKAS]',
                'deskripsi' => 'Status saat ini (Baru/Proses/Selesai/dll)',
            ],
            [
                'placeholder' => '[LUAS_TANAH]',
                'deskripsi' => 'Luas tanah yang dimohon (m²)',
            ],
            [
                'placeholder' => '[KETERANGAN]',
                'deskripsi' => 'Catatan atau keterangan tambahan pada berkas',
            ],
            [
                'placeholder' => '[TANGGAL_DAFTAR]',
                'deskripsi' => 'Tanggal berkas dibuat/didaftarkan',
            ],
            [
                'placeholder' => '[TANGGAL_UPDATE]',
                'deskripsi' => 'Tanggal terakhir data berkas diperbarui',
            ],

            // --- DATA PEMOHON (Relasi ke tabel kliens) ---
            [
                'placeholder' => '[NAMA_PEMOHON]',
                'deskripsi' => 'Nama lengkap pemohon/klien',
            ],
            [
                'placeholder' => '[NIK_PEMOHON]',
                'deskripsi' => 'NIK pemohon',
            ],
            [
                'placeholder' => '[ALAMAT_PEMOHON]',
                'deskripsi' => 'Alamat lengkap pemohon',
            ],
            [
                'placeholder' => '[NOMOR_HP_PEMOHON]',
                'deskripsi' => 'Nomor WhatsApp/HP pemohon',
            ],

            // --- DATA KEGIATAN (Relasi ke tabel jenis_permohonans) ---
            [
                'placeholder' => '[JENIS_KEGIATAN]',
                'deskripsi' => 'Nama jenis permohonan (misal: Konversi, Pemecahan)',
            ],

            // --- DATA LOKASI (Relasi ke tabel desas & kecamatans) ---
            [
                'placeholder' => '[NAMA_DESA]',
                'deskripsi' => 'Nama Desa letak tanah',
            ],
            [
                'placeholder' => '[NAMA_KECAMATAN]',
                'deskripsi' => 'Nama Kecamatan letak tanah',
            ],

            // --- DATA PENGUKURAN (Relasi ke tabel petugas_ukur) ---
            [
                'placeholder' => '[NAMA_PETUGAS_UKUR]',
                'deskripsi' => 'Nama petugas yang ditunjuk mengukur',
            ],
            [
                'placeholder' => '[JADWAL_UKUR]',
                'deskripsi' => 'Tanggal dan jam rencana pengukuran',
            ],
            [
                'placeholder' => '[STATUS_UKUR]',
                'deskripsi' => 'Status proses pengukuran',
            ],

            // --- DATA KUASA (Relasi ke tabel penerima_kuasas) ---
            [
                'placeholder' => '[NAMA_PENERIMA_KUASA]',
                'deskripsi' => 'Nama penerima kuasa (jika ada)',
            ],
            [
                'placeholder' => '[NOMOR_HP_KUASA]',
                'deskripsi' => 'Nomor HP penerima kuasa (jika ada)',
            ],
        ];

        foreach ($placeholders as $data) {
            WaPlaceholder::updateOrCreate(
                ['placeholder' => $data['placeholder']], // Kunci pencarian
                ['deskripsi' => $data['deskripsi']]      // Data yang diupdate
            );
        }
    }
}