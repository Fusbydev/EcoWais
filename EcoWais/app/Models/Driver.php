<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'phone_number', 'status'];

    // Relationship to User
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

// In Driver.php model
public function truck()
{
    return $this->hasOne(Truck::class, 'driver_id'); // Truck has driver_id column
}


}
