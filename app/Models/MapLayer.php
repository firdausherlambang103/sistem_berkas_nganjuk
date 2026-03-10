<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapLayer extends Model
{
    use HasFactory;

    // [PENTING] Paksa model ini menggunakan database PostgreSQL
    protected $connection = 'pgsql';

    // WAJIB: Masukkan SEMUA nama kolom agar bisa disimpan dari Form
    protected $fillable = [
        'nama_layer', 
        'tabel_db', 
        'warna',
        'tipe_layer', // INI YANG MEMBUAT TIPE LAYER BISA TERSIMPAN
        'color_hm',
        'color_hgb',
        'color_hp',
        'color_hgu',
        'color_wakaf'
    ];
}