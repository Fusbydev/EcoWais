<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

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
            'driver_id' => 'required|exists:drivers,id',
            'initial_location' => 'required|string',
            'initial_fuel' => 'required|integer|min:0|max:100',
        ]);

        // Create truck
        Truck::create([
            'truck_id' => $validated['truck_id'],
            'driver_id' => $validated['driver_id'],
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
        $trucks = Truck::with('driver')->get();

        $data = $trucks->map(function($truck) {
            $pickups = $truck->pickups;

            // If it's a string, decode it; if it's already an array, leave it
            if (is_string($pickups)) {
                $pickups = json_decode($pickups, true);
            } elseif (!is_array($pickups)) {
                $pickups = [];
            }

            return [
                'id'            => $truck->id,  // ✅ Add this line
                'truck_id'      => $truck->truck_id,
                'driver_name'   => $truck->driver->name ?? null,
                'initial_coords'=> $truck->initial_coords ?? null,
                'pickups'       => $pickups
            ];
        });

        return response()->json($data);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Truck $truck)
    {
        //
    }
}
