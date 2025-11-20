<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

        protected $fillable = [
        'location_id',
        'truck_id',
        'pickup_date',
        'pickup_time',
        'current_latitude',
        'current_longitude',
        'status', // optional if you want to set default status
    ];


    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
