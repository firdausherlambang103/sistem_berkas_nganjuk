<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapLayer extends Model
{
    use HasFactory;

    // [PENTING] Paksa model ini menggunakan database PostgreSQL
    protected $connection = 'pgsql';

    protected $fillable = ['nama_layer', 'tabel_db', 'warna'];
}