<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangayReport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BarangayReportController extends Controller
{
    public function store(Request $request)
    {
        // Log all incoming request data for debugging
        Log::info('Incoming report request:', $request->all());

        try {
            // Validate the request first (driver_id is now user_id)
            $validated = $request->validate([
                'issue_type'        => 'required|string',
                'other_issue'       => 'nullable|string',
                'driver_id'         => 'nullable|integer', // this is user_id now
                'adminId'          => 'required|integer',
                'location'          => 'required|string|max:255',
                'incident_datetime' => 'required|date',
                'description'       => 'required|string',
                'photo'             => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            ]);

            // Lookup the actual driver ID from the drivers table using user_id
            $driverId = null;
            if (!empty($validated['driver_id'])) {
                $driver = DB::table('drivers')->where('user_id', $validated['driver_id'])->first();
                if ($driver) {
                    $driverId = $driver->id;
                } else {
                    return redirect()->back()->with('error', 'No driver found for user_id: ' . $validated['driver_id'])->withInput();
                }
            }

            // Handle image upload
            $filePath = null;
            if ($request->hasFile('photo')) {
                $folderPath = public_path('assets/barangay_report_evidence');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
                $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->move($folderPath, $filename);
                $filePath = 'assets/barangay_report_evidence/' . $filename;
            }

            // Save report
            $report = BarangayReport::create([
                'issue_type'        => $validated['issue_type'],
                'other_issue'       => $validated['other_issue'] ?? null,
                'driver_id'         => $driverId,
                'adminId'          => $validated['adminId'],
                'location'          => $validated['location'],
                'incident_datetime' => $validated['incident_datetime'],
                'description'       => $validated['description'],
                'photo_path'        => $filePath,
            ]);

            if (!$report) {
                return redirect()->back()->with('error', 'Failed to save the report. Please try again.');
            }

            // Return success with the created report ID
            return redirect()->back()->with('success', 'Issue reported successfully! Report ID: ' . $report->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed: return all errors
            return redirect()->back()
                             ->withErrors($e->errors())
                             ->withInput();
        } catch (\Exception $e) {
            // Any other error
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

public function resolve($id)
{
    $report = BarangayReport::findOrFail($id);
    if($report->Status !== 'Resolved') {
        $report->Status = 'Resolved';
        $report->save();
    }
    return redirect()->back()->with('success1', 'Report marked as resolved.');
}



}
