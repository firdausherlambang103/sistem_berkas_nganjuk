<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_logs';

    protected $fillable = [
        'target_phone', // PENTING: Sesuaikan dengan DB
        'pesan',
        'status',
        'keterangan',   // PENTING: Sesuaikan dengan DB
        'berkas_id',
        'user_id',
        'template_id'
    ];

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