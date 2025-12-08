<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Display all users
    public function index()
    {
        $users = User::all(); // Retrieve all users from the DB
        return view('municipality-admin.user-management', compact('users'));
    }

    // Store a new user
public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'role'     => 'required|string',
            'status'   => 'required|in:activated,deactivated',
            'phone'    => 'nullable|string|max:20',
        ]);

        // Check if email exists manually
        if (\App\Models\User::where('email', $validated['email'])->exists()) {
            return redirect()->route('user-management')
                ->with('error', 'A user with this email already exists.');
        }

        // Create user
        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'role'         => $validated['role'],
            'status'       => $validated['status'],
            'phone_number' => $validated['phone'] ?? null,
        ]);

        // Send email verification
        event(new \Illuminate\Auth\Events\Registered($user));

        // Only create driver if role is barangay_waste_collector
        if ($validated['role'] === 'barangay_waste_collector') {
            \App\Models\Driver::create([
                'user_id'      => $user->id,
                'phone_number' => $validated['phone'] ?? null,
            ]);
        }

        return redirect()->route('user-management')
            ->with('success', 'User has been successfully created. A verification email has been sent.');

    } catch (\Exception $e) {
        dd($e->getMessage());
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
