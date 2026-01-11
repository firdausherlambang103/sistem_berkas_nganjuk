<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanBukuTanah extends Model
{
    use HasFactory;
    
    // Pastikan tabelnya benar
    protected $table = 'peminjaman_buku_tanahs';

    protected $fillable = [
        'user_id',
        'berkas_id', // <--- TAMBAHKAN INI
        'nomor_berkas',
        'jenis_hak',
        'nomor_hak',
        'desa_id',
        'kecamatan_id',
        'status',
        'catatan',
    ];

    public function berkas()
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }
    
    public function user() { return $this->belongsTo(User::class); }
    public function desa() { return $this->belongsTo(Desa::class); }
    public function kecamatan() { return $this->belongsTo(Kecamatan::class); }
}