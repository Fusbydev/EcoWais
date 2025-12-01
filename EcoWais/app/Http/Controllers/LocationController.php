<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location; // <-- Add this line

class LocationController extends Controller
{
     public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'location' => 'required|string|max:255',
            'adminId' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Create new location
        $location = new Location();
        $location->location = $request->location;
        $location->adminId = $request->adminId;
        $location->latitude = $request->latitude;
        $location->longitude = $request->longitude;
        $location->save();

        return redirect()->back()->with('success', 'Barangay location added successfully!');
    }
}
