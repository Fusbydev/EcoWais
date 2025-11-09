<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password']);
        }

        // Check role
        if ($user->role !== $request->role) {
            return back()->withErrors(['role' => 'Role does not match']);
        }

        // Login success, store in session
        session([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name, // adjust if your column is 'firstname'
        ]);


        // Redirect based on role
        switch ($user->role) {
            case 'barangay_admin':
               return redirect()->route('barangay.admin.homepage');
            case 'barangay_waste_collector':
                return redirect()->route('barangay.waste.collector.homepage'); // replace with actual page
            case 'municipality_administrator':
                return redirect()->route('municipality.dashboard');
            default:
                return redirect('/');
        }
    }
}
