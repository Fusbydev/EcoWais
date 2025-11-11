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
        'initial_location',
        'initial_fuel',
        'status',
    ];

     protected $casts = [
        'pickups' => 'array', // ✅ Automatically handle JSON encoding/decoding
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // In app/Models/Truck.php

public function location()
{
    return $this->belongsTo(Location::class, 'initial_location', 'location');
    // This joins: trucks.initial_location = locations.location
}
}
