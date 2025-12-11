<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Truck;
class UserController extends Controller
{
    // Display all users
    public function index()
    {
        $users = User::all(); // Retrieve all users from the DB
        $locations = Location::all();
        $trucks = Truck::all();
        return view('municipality-admin.user-management', compact('users', 'locations', 'trucks'));
    }

    // Store a new user
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|string',
            'phone'    => 'nullable|string|max:20',
            'location_id' => 'nullable|integer', // For admins
            'truck_id'    => 'nullable|integer', // For drivers
        ]);

        // Check if email exists manually
        if (User::where('email', $validated['email'])->exists()) {
            return redirect()->route('user-management')
                ->with('error', 'A user with this email already exists.');
        }

        // Create user
        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'role'         => $validated['role'],
            'status'       => 'activated',
            'phone_number' => $validated['phone'] ?? null,
        ]);

        // Send verification email
        event(new \Illuminate\Auth\Events\Registered($user));

        // If collector/driver
        if ($validated['role'] === 'barangay_waste_collector') {
            $driver = Driver::create([
                'user_id'      => $user->id,
                'phone_number' => $validated['phone'] ?? null,
            ]);

            // Assign driver to selected truck
            if (!empty($validated['truck_id'])) {
                $truck = Truck::find($validated['truck_id']);
                if ($truck) {
                    $truck->driver_id = $driver->id; // <-- Use the driver ID
                    $truck->save();
                }
            }
        }

        // If admin, assign location
        if ($validated['role'] === 'barangay_admin' && !empty($validated['location_id'])) {
            $location = Location::find($validated['location_id']);
            if ($location) {
                $location->adminId = $user->id;
                $location->save();
            }
        }

        return redirect()->route('user-management')
            ->with('success', 'User created successfully. Verification email sent.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', $e->getMessage());
    }
}








    // Update user
    public function update(Request $request, User $user)
{
    $request->validate([
        'name'   => 'required|string|max:255',
        'email'  => 'required|email|unique:users,email,' . $user->id,
        // remove role validation
        'phone'  => 'nullable|string|max:20',
        'status' => 'required|in:activated,deactivated',
    ]);

    $user->update([
        'name'   => $request->name,
        'email'  => $request->email,
        // do NOT update role
        'phone_number' => $request->phone,   // <-- FIXED
        'status' => $request->status,
    ]);

    return redirect()->route('user-management')->with('success', 'User updated successfully.');
}


    // Activate user
    public function activate(User $user)
    {
        $user->update(['status' => 'activated']);
        return redirect()->route('user-management')->with('success', 'User activated successfully.');
    }

    // Deactivate user
    public function deactivate(User $user)
    {
        $user->update(['status' => 'deactivated']);
        return redirect()->route('user-management')->with('success', 'User deactivated successfully.');
    }
}
