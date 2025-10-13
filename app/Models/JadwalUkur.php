<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUkur extends Model
{
    use HasFactory;
    protected $table = 'jadwal_ukur';
    protected $fillable = ['berkas_id', 'petugas_ukur_id', 'no_surat_tugas', 'tanggal_rencana_ukur', 'status_proses'];

    public function berkas()
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }

    public function petugasUkur()
    {
        return $this->belongsTo(PetugasUkur::class, 'petugas_ukur_id');
    }
}
