<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_logs';

    protected $fillable = [
        'target_phone', // UBAH: 'tujuan' menjadi 'target_phone' sesuai DB
        'pesan',
        'status',
        'keterangan',   // UBAH: 'error_message' menjadi 'keterangan' sesuai DB
        'berkas_id',
        'user_id',
        'template_id'
    ];

    // Relasi
    public function berkas()
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function template()
    {
        return $this->belongsTo(WaTemplate::class, 'template_id');
    }
}