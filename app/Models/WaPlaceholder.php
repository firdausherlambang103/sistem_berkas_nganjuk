<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaPlaceholder extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'example'];
}