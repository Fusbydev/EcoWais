<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\Truck;
use App\Models\Location;

class PickupController extends Controller
{
    // ✅ Show all pickups
    public function index()
    {
        $pickups = Pickup::with(['truck.driver', 'location'])->get();
        $trucks = Truck::all();
        $locations = Location::all();

        return view('municipality-admin.scheduling', compact('pickups', 'trucks', 'locations'));
    }
    // ✅ Store new pickup schedule
    public function store(Request $request)
    {
       $validated = $request->validate([
            'initial_location' => 'required|exists:locations,id',
            'truck' => 'required|exists:trucks,id',
            'admin-pickup-date' => 'required|date',
            'admin-pickup-time' => 'required',
        ]);

        // Fetch latitude and longitude from locations table
        $location = Location::find($validated['initial_location']);

        Pickup::create([
            'location_id' => $validated['initial_location'],
            'truck_id' => $validated['truck'],
            'pickup_date' => $validated['admin-pickup-date'],
            'pickup_time' => $validated['admin-pickup-time'],
            'current_latitude' => $location->latitude,
            'current_longitude' => $location->longitude,
        ]);

        return redirect()->route('municipality.scheduling')
                         ->with('pickupSuccess', 'Pickup schedule added successfully!');
    }

    // ✅ Delete a pickup schedule
    public function destroy($id)
    {
        $pickup = Pickup::findOrFail($id);
        $pickup->delete();

        return redirect()->route('municipality.scheduling')
                         ->with('success', 'Pickup schedule deleted successfully!');
    }

    public function getPickupLocations()
    {
        $pickups = Pickup::with(['truck.driver', 'location'])->get();

        $data = $pickups->map(function ($pickup) {
            if (!$pickup->location || !$pickup->truck) return null;

            return [
                'id' => $pickup->id,
                'barangay' => $pickup->location->location,
                'latitude' => $pickup->current_latitude,   // ✅ from pickups table
                'longitude' => $pickup->current_longitude, // ✅ from pickups table
                'truck_id' => $pickup->truck->truck_id,
                'status' => $pickup->truck->status, // ✅ include truck status
                'driver_name' => $pickup->truck->driver->user->name, // ✅ driver name
                'pickup_date' => $pickup->pickup_date,
                'pickup_time' => $pickup->pickup_time,
            ];
        })->filter()->values();

        return response()->json($data);
    }

    public function completePoint(Request $request, Pickup $pickup)
{
    $request->validate([
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
    ]);

    $lat = $request->input('lat');
    $lng = $request->input('lng');

    // Update the current_latitude/current_longitude
    $pickup->current_latitude = $lat;
    $pickup->current_longitude = $lng;

    // Append this point to completed_routes JSON
    $completedRoutes = $pickup->completed_routes ? json_decode($pickup->completed_routes, true) : [];
    $completedRoutes[] = ['lat' => $lat, 'lng' => $lng, 'completed_at' => now()];
    $pickup->completed_routes = json_encode($completedRoutes);

    $pickup->status = 'completed';
    $pickup->save();

    return response()->json(['success' => true]);
}



}
