<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaKuasa extends Model
{
    use HasFactory;

    protected $table = 'penerima_kuasas';

    protected $fillable = [
        'kode_kuasa',
        'nama_kuasa',
        'nomer_wa',
    ];

    // Relasi ke Berkas
    public function berkas()
    {
        return $this->hasMany(Berkas::class, 'penerima_kuasa_id');
    }
}