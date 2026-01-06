<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaPlaceholder extends Model
{
    use HasFactory;

    // Tambahkan ini agar tidak salah baca tabel
    protected $table = 'wa_placeholders';

    protected $fillable = ['code', 'description', 'example'];
}