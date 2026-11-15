<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    protected $fillable = ['villa_slug', 'start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
