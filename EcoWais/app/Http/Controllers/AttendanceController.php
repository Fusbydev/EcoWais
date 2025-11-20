<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    // Load trucks + driver + user info
    $collectors = \App\Models\Truck::with(['driver.user'])->get();

    return view('attendance.index', compact('collectors'));
}


    public function getCollectors()
{
    $collectors = \App\Models\Truck::with(['driver.user'])
        ->get()
        ->map(function($truck) {
            return [
                'driver_name' => $truck->driver->user->name ?? 'Unknown',
                'role' => 'Collector/Driver', // or dynamic if you have role column
                'truck_id' => $truck->truck_id,
            ];
        });

    return response()->json($collectors);
}
public function timeIn(Request $request)
{
    // Debug request
    \Log::debug('Time In Request:', $request->all());

    $attendance = Attendance::updateOrCreate(
        [
            'location_id' => $request->location_id,
            'user_id' => $request->user_id,
        ],
        [
            'time_in' => \Carbon\Carbon::now(),
            'status' => \Carbon\Carbon::now()->hour > 7 ? 'Late' : 'Present',
        ]
    );

    // Debug result
    \Log::debug('Time In Result:', $attendance->toArray());

    return redirect()->back(); // <-- this just refreshes the page
}




    public function timeOut(Request $request)
    {
        $attendance = Attendance::where('location_id', $request->location_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($attendance) {
            $attendance->update([
                'time_out' => Carbon::now(),
            ]);
        }

        return redirect()->back();
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
