<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPermohonan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_permohonan',
        'waktu_timeline_hari',
        'memerlukan_ukur', // <-- TAMBAHKAN INI
    ];

    /**
     * Mendefinisikan relasi ke Berkas.
     */
    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }
}
