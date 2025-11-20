<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_id',
        'driver_id',
        'location_id',   // <-- make sure this exists in your DB
        'initial_location',
        'initial_fuel',
        'status',
    ];

    protected $casts = [
        'pickups' => 'array',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function location()
{
    return $this->belongsTo(Location::class, 'initial_location', 'location');
}

}
