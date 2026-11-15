<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalRate extends Model
{
    protected $fillable = ['villa_slug', 'name', 'start_date', 'end_date', 'price'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];
}
