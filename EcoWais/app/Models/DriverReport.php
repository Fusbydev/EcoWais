<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverReport extends Model
{
    protected $fillable = [
        'driver_id',
        'issue_type',
        'description',
    ];
}

