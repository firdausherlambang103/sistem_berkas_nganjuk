<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Berkas extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomer_berkas','tahun', 'nama_pemohon', 'jenis_alas_hak', 'nomer_hak', 'jenis_permohonan_id',
        'kecamatan', 'desa', 'nomer_wa', 'catatan', 'posisi_sekarang_user_id', 'status',
        'status_pengiriman', 'pengirim_id', 'penerima_id', 'waktu_mulai_proses', 'waktu_selesai_proses',
        'penerima_kuasa_id', 'status_buku_tanah', 'petugas_ukur_id',
        
        // [DITAMBAHKAN] Field untuk Lampiran dan Peta Lokasi
        'file_sertipikat', 
        'file_data_pendukung', 
        'file_sps',
        'latitude', 
        'longitude'
    ];

    protected $casts = [
        'waktu_mulai_proses' => 'datetime',
        'waktu_selesai_proses' => 'datetime',
    ];

    // ===================================================================
    // RELASI
    // ===================================================================

    public function dataDesa()
    {
        return $this->belongsTo(Desa::class, 'desa');
    }

    public function dataKecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan');
    }

    public function petugasUkur()
    {
        return $this->belongsTo(PetugasUkur::class, 'petugas_ukur_id');
    }

    public function peminjamanBukuTanah()
    {
        return $this->hasOne(PeminjamanBukuTanah::class, 'berkas_id');
    }
    
    public function jenisPermohonan()
    {
        return $this->belongsTo(JenisPermohonan::class, 'jenis_permohonan_id');
    }

    public function posisiSekarang()
    {
        return $this->belongsTo(User::class, 'posisi_sekarang_user_id');
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatBerkas::class)->orderBy('created_at', 'desc');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    /**
     * Relasi 'user' untuk kompatibilitas dengan WaService.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'posisi_sekarang_user_id');
    }

    public function penerimaKuasa()
    {
        return $this->belongsTo(PenerimaKuasa::class, 'penerima_kuasa_id');
    }

    public function waLogs()
    {
        return $this->hasMany(WaLog::class, 'berkas_id');
    }

    // ===================================================================
    // ACCESSORS (MODIFIKASI PERBAIKAN TIMER)
    // ===================================================================

    /**
     * Menghitung lama proses.
     * [FIX] Berhenti menghitung jika status sudah Selesai/Ditutup.
     */
    public function getLamaProsesFormattedAttribute(): string
    {
        // Jika belum mulai, return strip
        if (is_null($this->waktu_mulai_proses)) return '-';

        // Tentukan waktu akhir perhitungan
        if ($this->waktu_selesai_proses) {
            // Jika ada waktu selesai, gunakan itu (STOP TIMER)
            $waktuAkhir = $this->waktu_selesai_proses;
        } elseif (in_array($this->status, ['Selesai', 'Ditutup'])) {
            // Jika status Selesai tapi kolom waktu_selesai_proses kosong (data lama),
            // gunakan updated_at sebagai fallback agar timer berhenti.
            $waktuAkhir = $this->updated_at;
        } else {
            // Jika masih diproses, gunakan waktu sekarang (TIMER JALAN)
            $waktuAkhir = Carbon::now();
        }

        // Return format human readable (contoh: "3 hari", "2 jam")
        return $this->waktu_mulai_proses->diffForHumans($waktuAkhir, true);
    }

    public function getJatuhTempoAttribute()
    {
        if ($this->waktu_mulai_proses && $this->jenisPermohonan) {
            return $this->waktu_mulai_proses->addDays($this->jenisPermohonan->waktu_timeline_hari);
        }
        return null;
    }

    /**
     * Menghitung sisa waktu / keterlambatan.
     * [FIX] Return 'Selesai' jika berkas sudah rampung.
     */
    public function getSisaWaktuAttribute(): string
    {
        // Jika berkas sudah selesai/ditutup, tidak perlu hitung sisa waktu
        if (in_array($this->status, ['Selesai', 'Ditutup'])) {
            return 'Selesai'; 
        }

        $jatuhTempo = $this->getJatuhTempoAttribute();
        
        // Jika belum ada jatuh tempo atau status bukan Diproses/Pending
        if (is_null($jatuhTempo)) return '-';

        $now = Carbon::now();
        
        return $now->greaterThan($jatuhTempo) 
            ? 'Lewat ' . $jatuhTempo->diffForHumans($now, true) 
            : 'Sisa ' . $now->diffForHumans($jatuhTempo, true);
    }
}