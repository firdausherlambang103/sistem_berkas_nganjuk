<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensusWakaf extends Model
{
    use HasFactory;

    protected $fillable = [
        'rec_time',
        'latitude',
        'longitude',
        'penggunaan',
        'pengenal',
        'status_tanah',
        'afiliasi',
        'kecamatan',
        'desa',
    ];
}