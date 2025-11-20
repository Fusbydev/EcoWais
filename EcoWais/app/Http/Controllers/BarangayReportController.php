<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangayReport;

class BarangayReportController extends Controller
{
    public function store(Request $request)
    {
        // Wrap everything in a try/catch
        try {
            // Validate the request
            $validated = $request->validate([
                'issue_type'        => 'required|string',
                'other_issue'       => 'nullable|string',
                'driver_id'         => 'nullable|exists:drivers,id',
                'location'          => 'required|string|max:255',
                'incident_datetime' => 'required|date',
                'priority'          => 'required|in:low,medium,high',
                'description'       => 'required|string',
                'photo'             => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            ]);

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
                'driver_id'         => $validated['driver_id'] ?? null,
                'location'          => $validated['location'],
                'incident_datetime' => $validated['incident_datetime'],
                'priority'          => $validated['priority'],
                'description'       => $validated['description'],
                'photo_path'        => $filePath,
            ]);

            if (!$report) {
                // If saving fails
                return redirect()->back()->with('error', 'Failed to save the report. Please try again.');
            }

            return redirect()->back()->with('success', 'Issue reported successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed
            return redirect()->back()
                             ->withErrors($e->errors())
                             ->withInput();
        } catch (\Exception $e) {
            // Any other error
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
