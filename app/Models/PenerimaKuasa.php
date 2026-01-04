<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaKuasa extends Model
{
    use HasFactory;

    // Nama tabel di database (opsional jika sesuai konvensi, tapi baik untuk kejelasan)
    protected $table = 'penerima_kuasas';

    protected $fillable = [
        'kode_kuasa',
        'nama_kuasa',
        'nomer_wa',
    ];

    /**
     * Relasi: Satu Penerima Kuasa bisa mengurus banyak Berkas.
     */
    public function berkas()
    {
        return $this->hasMany(Berkas::class, 'penerima_kuasa_id');
    }
}