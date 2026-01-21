<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_logs';

    // Tambahkan 'berkas_id' dan 'user_id' ke fillable
    protected $fillable = [
        'tujuan', 
        'pesan', 
        'status', 
        'error_message',
        'berkas_id', // <-- PENTING
        'user_id',   // <-- PENTING
        'template_id'
    ];

    // Relasi ke Berkas (Opsional, untuk mempermudah tracking)
    public function berkas()
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}