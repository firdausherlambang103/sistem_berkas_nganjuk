<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    use HasFactory;

    protected $table = 'wa_templates';

    // PERBAIKAN: Sesuaikan dengan kolom database Anda
    protected $fillable = [
        'nama',      // Di form: nama_template
        'template',  // Di form: isi_pesan
        'status',    // Status aktif/tidak aktif
    ];
}