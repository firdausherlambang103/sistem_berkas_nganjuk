<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $table = 'wa_logs';

    protected $fillable = [
        'tujuan',
        'pesan',
        'status',
        'error_message',
        'berkas_id',   // <--- WAJIB ADA
        'user_id',     // <--- WAJIB ADA
        'template_id'  // <--- WAJIB ADA
    ];

    // Relasi (Opsional tapi berguna)
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