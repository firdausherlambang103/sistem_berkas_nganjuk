<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetugasUkur extends Model
{
    use HasFactory;
    protected $table = 'petugas_ukur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // <-- PASTIKAN BARIS INI ADA
        'keahlian',
    ];

    /**
     * Relasi ke model User.
     * Setiap PetugasUkur terhubung ke satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model JadwalUkur.
     * Satu PetugasUkur bisa memiliki banyak JadwalUkur.
     */
    public function jadwalUkur()
    {
        return $this->hasMany(JadwalUkur::class, 'petugas_ukur_id');
    }

    /**
     * Relasi many-to-many ke model Kecamatan melalui tabel pivot.
     */
    public function areaKerja()
    {
        return $this->belongsToMany(Kecamatan::class, 'area_kerja_petugas_ukur', 'petugas_ukur_id', 'kecamatan_id');
    }
}

