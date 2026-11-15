<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaUnit extends Model
{
    protected $fillable = ['villa_slug', 'units'];

    protected $casts = ['units' => 'integer'];
}
