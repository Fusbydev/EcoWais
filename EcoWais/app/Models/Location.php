<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    // If your table name is not the plural of the model, specify it
    // protected $table = 'locations';

    // Specify which fields are mass assignable
    protected $fillable = ['location'];

    // If you don’t have timestamps in the table
    public $timestamps = false;
}
