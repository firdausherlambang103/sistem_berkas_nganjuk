<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatBerkas extends Model
{
    use HasFactory;

    protected $fillable = [
        'berkas_id', 'dari_user_id', 'ke_user_id',
        'waktu_kirim', 'catatan_pengiriman'
    ];

    // Relasi: Satu Riwayat pasti terkait dengan satu User pengirim
    public function dariUser()
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    // Relasi: Satu Riwayat pasti terkait dengan satu User penerima
    public function keUser()
    {
        return $this->belongsTo(User::class, 'ke_user_id');
    }
}