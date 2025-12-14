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
    // Validate the request
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'role'     => 'required|string',
        'phone'    => 'nullable|string|size:11',
        'location_id' => 'nullable|integer',
        'truck_id'    => 'nullable|integer',
    ], [
        'email.unique' => 'A user with this email already exists.',
        'phone.size' => 'Phone number must be exactly 11 digits.',
        'password.confirmed' => 'Password confirmation does not match.',
        'password.min' => 'Password must be at least 6 characters.',
    ]);

    try {
        // Create user
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'status'       => 'activated',
            'phone_number' => $request->phone ?? null,
        ]);

        // Send verification email
        event(new \Illuminate\Auth\Events\Registered($user));

        // If collector/driver
        if ($request->role === 'barangay_waste_collector') {
            $driver = Driver::create([
                'user_id'      => $user->id,
                'status'       => 'break',
                'phone_number' => $request->phone ?? null,
            ]);

            // Assign driver to selected truck
            if (!empty($request->truck_id)) {
                $truck = Truck::find($request->truck_id);
                if ($truck) {
                    $truck->driver_id = $driver->id;
                    $truck->save();
                }
            }
        }

        // If admin, assign location
        if ($request->role === 'barangay_admin' && !empty($request->location_id)) {
            $location = Location::find($request->location_id);
            if ($location) {
                $location->adminId = $user->id;
                $location->save();
            }
        }

        return redirect()->route('user-management')
            ->with('success', 'User created successfully. Verification email sent.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'An error occurred: ' . $e->getMessage());
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
