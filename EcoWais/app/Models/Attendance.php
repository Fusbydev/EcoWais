<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'user_id',
        'time_in',
        'time_out',
        'status',
    ];

    // This is the key fix:
    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];
}
