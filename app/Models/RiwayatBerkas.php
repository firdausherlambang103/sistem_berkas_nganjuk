<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatBerkas extends Model
{
    use HasFactory;

    protected $table = 'riwayat_berkas';
    protected $guarded = ['id'];

    // Relasi ke Berkas Utama
    public function berkas()
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }

    // Relasi ke User Pengirim (Digunakan untuk logika Argo Loket Pembayaran)
    public function dariUser()
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    // Relasi ke User Penerima (Digunakan untuk menampilkan kepada siapa berkas dikirim)
    public function keUser()
    {
        return $this->belongsTo(User::class, 'ke_user_id');
    }
}