<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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

    // Check status
    if ($user->status === 'deactivated') {
        return back()->withErrors(['status' => 'Your account is deactivated. Please contact the administrator.']);
    }

    // Check password
    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors(['password' => 'Invalid password']);
    }

    // Check role
    if ($user->role !== $request->role) {
        return back()->withErrors(['role' => 'Role does not match']);
    }

    // ✅ Barangay admin must be assigned to a location
    if ($user->role === 'barangay_admin') {
        $isAssigned = \App\Models\Location::where('adminId', $user->id)->exists();

        if (!$isAssigned) {
            return back()->withErrors([
                'location' => 'Your account is not assigned to any barangay. Please contact the administrator.'
            ]);
        }
    }

    // Check if email is verified
    if (!$user->hasVerifiedEmail()) {
        return back()->withErrors(['email' => 'You need to verify your email before logging in.']);
    }

    // Login success - store session
    session([
        'user_id' => $user->id,
        'user_role' => $user->role,
        'user_name' => $user->name,
    ]);

    // Redirect based on role
    switch ($user->role) {
        case 'barangay_admin':
            return redirect()->route('barangay.admin.homepage');

        case 'barangay_waste_collector':
            return redirect()->route('barangay.waste.collector.homepage');

        case 'municipality_administrator':
            return redirect()->route('municipality.admin');

        default:
            return redirect('/');
    }
}



public function showLoginForm()
{
    // Since your Blade is in views/login.blade.php, just return it:
    return view('login');
}

public function showForgotPasswordForm()
{
    return view('auth.forgot-password'); // create this Blade
}

public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email|exists:users,email']);

    $token = Str::random(64);

    DB::table('password_resets')->updateOrInsert(
        ['email' => $request->email],
        ['token' => $token, 'created_at' => Carbon::now()]
    );

    Mail::send('auth.emails.password-reset', ['token' => $token, 'email' => $request->email], function ($message) use ($request) {
        $message->to($request->email);
        $message->subject('Reset your password');
    });

    return back()->with('status', 'We emailed your password reset link!');
}

public function showResetForm($token)
{
    return view('auth.reset-password', ['token' => $token]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|confirmed|min:6',
        'token' => 'required'
    ]);

    $record = DB::table('password_resets')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$record) {
        return back()->withErrors(['email' => 'Invalid token or email.']);
    }

    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_resets')->where('email', $request->email)->delete();

    return redirect('/login')->with('status', 'Password reset successfully!');
}

}
