<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;      // ✅ Import User model
use App\Models\Driver;    // ✅ Import Driver model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ✅ Import Hash facade
use Illuminate\Support\Str;
use App\Models\Location;
use App\Models\Truck;
use App\Models\Attendance;


class DriverController extends Controller
{
   // Store new driver + user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
        ]);

        // Generate random password
        $passwordPlain = Str::random(8);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($passwordPlain),
            'role' => 'barangay_waste_collector'
        ]);

        // Create driver with the user_id
        Driver::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone'],
        ]);

        // Redirect back with password info
        return redirect()->route('municipality.admin')->with([
            'success' => "Driver '{$user->name}' added successfully!",
            'generated_password' => $passwordPlain,
            'generated_email' => $user->email
        ]);
    }

public function getCollectors($barangay)
{
    $collectors = \App\Models\Truck::where('initial_location', $barangay)
        ->with(['driver.user'])
        ->get()
        ->map(function ($truck) {
            return [
                'driver_name' => $truck->driver->user->name ?? 'Unknown Driver',
                'truck_id' => $truck->truck_id,
            ];
        });

    return response()->json($collectors);
}

public function getTrucks($id)
{
    $location = Location::find($id);
    if (!$location) {
        return response()->json([]);
    }

    $today = now()->toDateString();

    $trucks = Truck::with([
        'driver.user',
        'driver.user.attendances' => function($query) use ($id, $today) {
            $query->where('location_id', $id)
                  ->whereDate('created_at', $today);
        }
    ])
    ->where('initial_location', $location->location)
    ->get()
    ->map(function($truck) {
        $attendance = $truck->driver->user->attendances->first();

        return [
            'id' => $truck->id,
            'user_id' => $truck->driver->user->id,
            'truck_id' => $truck->truck_id,
            'driver_name' => $truck->driver->user->name ?? 'No Driver Assigned',
            'role' => $truck->driver->user->role ?? 'N/A',
            'time_in' => $attendance->time_in ?? null,
            'time_out' => $attendance->time_out ?? null,
            'status' => $attendance->status ?? 'Absent',
        ];
    });

    return response()->json($trucks);
}




}
