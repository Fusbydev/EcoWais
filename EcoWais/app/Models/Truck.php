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

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
