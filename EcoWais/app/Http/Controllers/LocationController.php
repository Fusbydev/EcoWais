<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location; // <-- Add this line
use App\Models\User;

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

    public function manageLocation()
    {
        $locations = Location::all();
        $users = User::all();
        return view('municipality-admin.manage-location', compact('locations', 'users'));
    }

    public function assignAdmin(Request $request)
{
    $request->validate([
        'location_id' => 'required|exists:locations,id',
        'adminId' => 'required|exists:users,id',
    ]);

    $location = Location::find($request->location_id);
    $location->adminId = $request->adminId;
    $location->save();

    return redirect()->back()->with('success', 'Admin successfully assigned to location!');
}

}
