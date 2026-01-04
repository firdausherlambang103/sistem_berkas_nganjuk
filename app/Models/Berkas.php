<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Berkas extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomer_berkas', 'nama_pemohon', 'jenis_alas_hak', 'nomer_hak', 'jenis_permohonan_id',
        'kecamatan', 'desa', 'nomer_wa', 'catatan', 'posisi_sekarang_user_id', 'status',
        'status_pengiriman', 'pengirim_id', 'penerima_id', 'waktu_mulai_proses', 'waktu_selesai_proses','penerima_kuasa_id',
    ];

    protected $casts = [
        'waktu_mulai_proses' => 'datetime',
        'waktu_selesai_proses' => 'datetime',
    ];

    // ===================================================================
    // RELASI
    // ===================================================================

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

    // ===================================================================
    // ACCESSORS
    // ===================================================================

    /**
     * Accessor untuk menghitung lama proses dalam format yang mudah dibaca.
     */
    public function getLamaProsesFormattedAttribute(): string
    {
        if (is_null($this->waktu_mulai_proses)) {
            return '-';
        }

        $waktuMulai = $this->waktu_mulai_proses;
        $waktuAkhir = $this->waktu_selesai_proses ?? Carbon::now();

        return $waktuMulai->diffForHumans($waktuAkhir, true);
    }

    /**
     * Accessor BARU: Menghitung tanggal jatuh tempo.
     * Jatuh tempo dihitung dari 'waktu_mulai_proses' ditambah timeline dari jenis permohonan.
     */
    public function getJatuhTempoAttribute()
    {
        if ($this->waktu_mulai_proses && $this->jenisPermohonan) {
            return $this->waktu_mulai_proses->addDays($this->jenisPermohonan->waktu_timeline_hari);
        }
        return null;
    }

    /**
     * Accessor BARU: Menghitung sisa waktu sebelum jatuh tempo.
     */
    public function getSisaWaktuAttribute(): string
    {
        $jatuhTempo = $this->getJatuhTempoAttribute();

        if (is_null($jatuhTempo) || $this->status !== 'Diproses') {
            return '-';
        }

        $now = Carbon::now();
        
        if ($now->greaterThan($jatuhTempo)) {
            return 'Lewat ' . $jatuhTempo->diffForHumans($now, true);
        }

        return 'Sisa ' . $now->diffForHumans($jatuhTempo, true);
    }
}
