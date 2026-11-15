<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YogaBooking extends Model
{
    public const TYPES = ['retreat', 'class', 'private'];

    protected $fillable = [
        'reference', 'type', 'option_title', 'session', 'start_date', 'end_date', 'nights',
        'time_pref', 'participants', 'level', 'name', 'email', 'phone', 'country', 'notes',
        'status', 'terms_accepted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'terms_accepted_at' => 'datetime',
    ];
}
