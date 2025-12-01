<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',   // Barangay name
        'adminId',    // Assigned admin user ID
        'latitude',
        'longitude',
    ];

    // Timestamps are enabled (created_at, updated_at)
    public $timestamps = true;

    // Optional: define relationship to User (admin)
    public function admin()
    {
        return $this->belongsTo(User::class, 'adminId');
    }
}
