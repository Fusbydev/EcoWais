<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverReport extends Model
{
    protected $table = 'driver_reports';
    
    protected $fillable = [
        'driver_id',
        'status',
        'priority',
        'location',
        'issue_type',
        'description',
    ];

    // Explicitly enable timestamps (this is default, but let's be sure)
    public $timestamps = true;

    /**
     * Get the driver that owns the report
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}