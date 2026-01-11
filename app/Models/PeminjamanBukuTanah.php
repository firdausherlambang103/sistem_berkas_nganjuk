<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanBukuTanah extends Model
{
    use HasFactory;

    // PENTING: Ini mengizinkan semua kolom diisi (kecuali id)
    // Jika baris ini tidak ada, akan muncul error "Add [kolom] to fillable property"
    protected $guarded = ['id'];

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}