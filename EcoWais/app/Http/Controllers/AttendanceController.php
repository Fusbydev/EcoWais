<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\Response;

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
    \Log::debug('Incoming Time In Request', $request->all());

    $now = \Carbon\Carbon::now('Asia/Manila');
    $hour = (int) $now->format('H');
    $status = ($hour >= 1 && $hour < 7) ? 'Present' : 'Late';

    $pickupSession = $request->input('session_pickup');

    \Log::debug('Time In Variables', [
        'now' => $now,
        'status' => $status,
        'pickupSession' => $pickupSession
    ]);

    // Check if attendance already exists for this user and session
    $existing = Attendance::where('user_id', $request->user_id)
        ->where('pickupSession', $pickupSession)
        ->first();

    if ($existing) {
        \Log::debug('Attendance already exists for this driver and pickup date', $existing->toArray());
        return redirect()->back()->with('message', 'Attendance already recorded for this session.');
    }

    // Create a new attendance row
    $attendance = Attendance::create([
        'user_id' => $request->user_id,
        'location_id' => $request->location_id,
        'pickupSession' => $pickupSession,
        'time_in' => $now,
        'status' => $status
    ]);

    \Log::debug('New Attendance Created', $attendance->toArray());

    return redirect()->back()->with('message', 'Attendance recorded successfully.');
}








public function timeOut(Request $request)
{
    $pickupSession = $request->input('session_pickup');

    // Get the attendance record for this user, location, and session
    $attendance = Attendance::where('location_id', $request->location_id)
        ->where('user_id', $request->user_id)
        ->where('pickupSession', $pickupSession)
        ->latest() // get the most recent if multiple exist
        ->first();

    if ($attendance) {
        $attendance->update([
            'time_out' => \Carbon\Carbon::now('Asia/Manila'), // Manila timezone
        ]);
    } else {
        \Log::debug('Time Out: Attendance not found', [
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'pickupSession' => $pickupSession,
        ]);
    }

    return redirect()->back();
}



public function exportPdf()
{
    // Get all attendance records
    $attendance = Attendance::all();
    $users = \App\Models\User::all();
    $locations = \App\Models\Location::all();

    $pdf = PDF::loadView('municipality-admin.attendance-pdf', compact('attendance', 'users', 'locations'));

    return $pdf->download('attendance_report.pdf');
}

public function exportCsv()
{
    $attendance = Attendance::with(['user', 'location'])->get();

    $filename = 'attendance_report_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $columns = ['Driver Name', 'Barangay', 'Pickup Date', 'Time In', 'Time Out', 'Hours Worked', 'Status'];

    $callback = function() use ($attendance, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($attendance as $att) {
            $hoursWorked = '-';
            if ($att->time_in && $att->time_out) {
                $hoursWorked = number_format(
                    \Carbon\Carbon::parse($att->time_in)
                        ->floatDiffInHours(\Carbon\Carbon::parse($att->time_out)),
                    2
                );
            }

            fputcsv($file, [
                $att->user->name ?? 'Unknown',
                $att->location->location ?? 'Unknown',
                $att->pickupSession ?? '-',
                $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('Y-m-d H:i:s') : '-',
                $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('Y-m-d H:i:s') : '-',
                $hoursWorked,
                $att->status,
            ]);
        }

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
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
