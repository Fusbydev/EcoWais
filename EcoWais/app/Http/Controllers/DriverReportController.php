<?php

namespace App\Http\Controllers;

use App\Models\DriverReport;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverReportController extends Controller
{
    public function store(Request $request)
{
    try {
        Log::info('Driver Report Incoming Data: ' . json_encode($request->all()));

        // Get the driver record linked to the user
        $driver = Driver::where('user_id', $request->driver_id)->first();

        if (!$driver) {
            return redirect()->back()->with('error', 'No driver record found for the current user.');
        }

        // Determine issue type
        $issueType = $request->issue_type;
        if ($issueType === 'other' && $request->other_issue) {
            $issueType = $request->other_issue;
        }

        // Store the report
        DriverReport::create([
            'driver_id' => $driver->id,
            'issue_type' => $issueType,
            'description' => $request->description,
        ]);

        // Redirect back to homepage with success message
        return redirect()->route('barangay.waste.collector.homepage')
                         ->with('success', 'Report submitted successfully.');

    } catch (\Exception $e) {
        Log::error('Driver Report Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}

}
