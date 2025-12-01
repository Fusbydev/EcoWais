<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteCollection extends Model
{
    protected $fillable = [
        'location_id',
        'truck_id',
        'collector_id',
        'waste_type',
        'kilos',
        'pickup_date',
    ];
}

