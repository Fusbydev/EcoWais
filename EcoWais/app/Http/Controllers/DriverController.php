<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;      // ✅ Import User model
use App\Models\Driver;    // ✅ Import Driver model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ✅ Import Hash facade
use Illuminate\Support\Str;


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
}
