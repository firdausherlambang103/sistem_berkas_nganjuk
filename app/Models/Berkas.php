<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Berkas extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomer_berkas', 'tahun', 'nama_pemohon', 'jenis_alas_hak', 'nomer_hak', 'jenis_permohonan_id',
        'kecamatan', 'desa', // <-- INI NAMA KOLOM (JANGAN DIUBAH)
        'nomer_wa', 'catatan', 'posisi_sekarang_user_id', 'status',
        'status_pengiriman', 'pengirim_id', 'penerima_id', 'waktu_mulai_proses', 'waktu_selesai_proses',
        'penerima_kuasa_id', 'status_buku_tanah', 'petugas_ukur_id'
    ];

    protected $casts = [
        'waktu_mulai_proses' => 'datetime',
        'waktu_selesai_proses' => 'datetime',
    ];

    // ===================================================================
    // RELASI (DIPERBAIKI: Menggunakan awalan 'data' untuk hindari crash)
    // ===================================================================

    // [UBAH] Dari 'desa' menjadi 'dataDesa'
    public function dataDesa()
    {
        return $this->belongsTo(Desa::class, 'desa');
    }

    // [UBAH] Dari 'kecamatan' menjadi 'dataKecamatan'
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

    public function penerimaKuasa()
    {
        return $this->belongsTo(PenerimaKuasa::class, 'penerima_kuasa_id');
    }

    public function waLogs()
    {
        return $this->hasMany(WaLog::class, 'berkas_id');
    }

    // ... (ACCESSORS TETAP SAMA SEPERTI SEBELUMNYA) ...
    public function getLamaProsesFormattedAttribute(): string
    {
        if (is_null($this->waktu_mulai_proses)) return '-';
        $waktuAkhir = $this->waktu_selesai_proses ?? Carbon::now();
        return $this->waktu_mulai_proses->diffForHumans($waktuAkhir, true);
    }

    public function getJatuhTempoAttribute()
    {
        if ($this->waktu_mulai_proses && $this->jenisPermohonan) {
            return $this->waktu_mulai_proses->addDays($this->jenisPermohonan->waktu_timeline_hari);
        }
        return null;
    }

    public function getSisaWaktuAttribute(): string
    {
        $jatuhTempo = $this->getJatuhTempoAttribute();
        if (is_null($jatuhTempo) || $this->status !== 'Diproses') return '-';
        $now = Carbon::now();
        return $now->greaterThan($jatuhTempo) 
            ? 'Lewat ' . $jatuhTempo->diffForHumans($now, true) 
            : 'Sisa ' . $now->diffForHumans($jatuhTempo, true);
    }
}