<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    use HasFactory;

    protected $table = 'wa_templates';

    protected $fillable = [
        'nama',
        'template',
        'status',
    ];
}