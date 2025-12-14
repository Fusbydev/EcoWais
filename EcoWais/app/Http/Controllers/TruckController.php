<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Support\Facades\Http;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class TruckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'truck_id' => 'required|string|unique:trucks,truck_id',
        'driver_id' => 'nullable|exists:drivers,id', // <-- now optional
        'initial_location' => 'required|string',
        'initial_fuel' => 'required|integer|min:0|max:100',
    ]);

    // Create truck
    Truck::create([
        'truck_id' => $validated['truck_id'],
        'driver_id' => $validated['driver_id'] ?? null, // <-- default to null if not provided
        'initial_location' => $validated['initial_location'],
        'initial_fuel' => $validated['initial_fuel'],
        'status' => 'idle', // default status
    ]);

    // Redirect back with success message
    return redirect()->back()->with('truckSuccess', "Truck '{$validated['truck_id']}' added successfully!");
}


    public function updatePickups(Request $request)
    {
        $request->validate([
            'truck_id' => 'required|exists:trucks,id',
            'pickups' => 'required|array',
        ]);

        $truck = Truck::find($request->truck_id);
        $truck->pickups = $request->pickups;
        $truck->save();

        return response()->json(['success' => true]);
    }

public function getTruckPickups()
{
    try {
        $userId = session('user_id'); // or Auth::id()
        $user = \App\Models\User::find($userId);
        $userRole = $user->role ?? null;

        // Base query: trucks with tracking = 'True'
        $trucksQuery = Truck::with('driver.user')
            ->where('tracking', 'True');

        // Only waste collector's trucks
        if ($userRole === 'barangay_waste_collector') {
            $trucksQuery->whereHas('driver', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $trucks = $trucksQuery->get();

        $data = $trucks->map(function ($truck) {
            // Decode pickups JSON if needed
            $pickups = $truck->pickups;
            if (is_string($pickups)) {
                $pickups = json_decode($pickups, true);
            } elseif (!is_array($pickups)) {
                $pickups = [];
            }

            // Decode initial_coords if stored as JSON
            $initial_coords = $truck->initial_coords;
            if (is_string($initial_coords)) {
                $initial_coords = json_decode($initial_coords, true);
            }

            // 🔹 Get pickup dates from pickups table
            $pickupDate = \DB::table('pickups')
    ->where('truck_id', $truck->id)
    ->value('pickup_date'); // returns single value or null


            return [
                'id'             => $truck->id,
                'truck_id'       => $truck->truck_id,
                'pickup_date'   => $pickupDate, // 🔹 added
                'status'         => $truck->status,
                'driver_name'    => $truck->driver->user->name ?? null,
                'user_id'        => $truck->driver->user_id ?? null,
                'initial_coords' => $initial_coords ?? null,
                'pickups'        => $pickups,
                
            ];
        });

        return response()->json($data);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}





public function getDriverPickupAddressesByUser(Request $request)
{
    // 1️⃣ Get user ID from session or input
    $userId = session('user_id'); // or from your session

    if (!$userId) {
        return response()->json(['message' => 'User not logged in'], 401);
    }

    // 2️⃣ Get driver ID
    $driver = DB::table('drivers')->where('user_id', $userId)->first();
    if (!$driver) {
        return response()->json(['message' => 'No driver found'], 404);
    }

    $driverId = $driver->id;

    // 3️⃣ Get truck assigned to this driver
    $truck = Truck::where('driver_id', $driverId)->first();
    if (!$truck || empty($truck->pickups)) {
        return response()->json(['message' => 'No pickups found'], 404);
    }

    // 4️⃣ Prepare pickups with readable addresses
    $pickups = [];
    foreach ($truck->pickups as $point) {
        $lat = $point['lat'];
        $lng = $point['lng'];

        // Reverse geocode with OpenStreetMap Nominatim
        $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
            'format' => 'json',
            'lat' => $lat,
            'lon' => $lng,
            'zoom' => 18,
            'addressdetails' => 1
        ]);

        $address = $lat . ', ' . $lng; // fallback if API fails
        if ($response->ok() && isset($response['display_name'])) {
            $address = $response['display_name'];
        }

        $pickups[] = [
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => $address,
            'timeWindow' => $point['timeWindow'] ?? null
        ];
    }

    return response()->json($pickups);
}



public function updateTracking(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:drivers,user_id',
        'tracking' => 'nullable', // checkbox unchecked will be null
    ]);

    // Get driver by user_id
    $driver = \App\Models\Driver::where('user_id', $request->user_id)->firstOrFail();

    // Determine tracking value: string 'true' or 'false'
    $trackingValue = $request->has('tracking') ? 'true' : 'false';

    // Update all trucks assigned to this driver
    \App\Models\Truck::where('driver_id', $driver->id)->update([
        'tracking' => $trackingValue
    ]);

    return redirect()->back()->with('success', 'Tracking updated!');
}


    /**
     * Display the specified resource.
     */
    public function show(Truck $truck)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Truck $truck)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Truck $truck)
    {
        // Validate input
        $validated = $request->validate([
            'truck_id' => 'required|string|max:255',
            'driver_id' => 'nullable|exists:drivers,id',
            'initial_location' => 'nullable|string|max:255',
            'status' => 'required|in:active,idle,maintenance',
            'initial_fuel' => 'required|numeric|min:0|max:100',
        ]);

        // Update the truck
        $truck->update($validated);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Truck updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
        public function setIdle($id)
{
    $truck = Truck::findOrFail($id);

    // If it's an AJAX request (auto-trigger), only set to idle
    if (request()->wantsJson()) {
        // Only update if not already idle
        if ($truck->status !== 'idle') {
            $truck->status = 'idle';
            $truck->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Truck status updated successfully.',
            'status' => $truck->status
        ]);
    }

    // For form submissions (manual button), toggle between active/idle
    $truck->status = ($truck->status === 'active') ? 'idle' : 'active';
    $truck->save();

    return back()->with('success', 'Truck status updated successfully.');
}

}
