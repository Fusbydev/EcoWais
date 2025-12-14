<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location; // <-- Add this line
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
class LocationController extends Controller
{
public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'location.required' => 'The barangay location is required.',
            'location.max' => 'The barangay location must not exceed 255 characters.',
            'latitude.required' => 'The latitude is required.',
            'latitude.numeric' => 'The latitude must be a valid number.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
            'longitude.required' => 'The longitude is required.',
            'longitude.numeric' => 'The longitude must be a valid number.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
        ]);

        try {
            Location::create([
                'location'  => $validated['location'],
                'latitude'  => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            return redirect()
                ->back()
                ->with('success', 'Barangay location added successfully!');

        } catch (QueryException $e) {
            // Check if it's a duplicate entry error (error code 23000 or 1062)
            if ($e->getCode() == 23000 || $e->errorInfo[1] == 1062) {
                return redirect()
                    ->back()
                    ->with('error', 'This barangay location already exists.')
                    ->withInput();
            }
            
            // Catch any other database errors
            return redirect()
                ->back()
                ->with('error', 'An error occurred while saving the location. Please try again.')
                ->withInput();
        } catch (Exception $e) {
            // Catch any other unexpected errors
            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred. Please try again.')
                ->withInput();
        }
    }



    public function manageLocation()
    {
        $locations = Location::all();
        $users = User::all();
        return view('municipality-admin.manage-location', compact('locations', 'users'));
    }

    public function assignAdmin(Request $request)
{
    $request->validate([
        'location_id' => 'required|exists:locations,id',
        'adminId' => 'required|exists:users,id',
    ]);

    $location = Location::find($request->location_id);
    $location->adminId = $request->adminId;
    $location->save();

    return redirect()->back()->with('success', 'Admin successfully assigned to location!');
}

}
