<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangayReport extends Model
{
    protected $table = 'reports'; // or 'reports' if that’s the table
    protected $fillable = [
        'issue_type',
        'other_issue',
        'driver_id',
        'location',
        'incident_datetime',
        'priority',
        'description',
        'photo_path',
    ];
}
