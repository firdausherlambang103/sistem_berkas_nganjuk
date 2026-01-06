<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaPlaceholder extends Model
{
    use HasFactory;

    protected $table = 'wa_placeholders';

    protected $fillable = [
        'placeholder',
        'deskripsi',
    ];
}