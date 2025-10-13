<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;
    protected $fillable = ['nama_kecamatan'];

    /**
     * Relasi: Satu Kecamatan bisa menjadi area kerja banyak Petugas Ukur
     */
    public function petugasUkur()
    {
        return $this->belongsToMany(PetugasUkur::class, 'area_kerja_petugas_ukur');
    }
}
