<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use DB;
use Carbon\Carbon;
use App\Models\BarangayReport;

class ReportController extends Controller
{
    // Page with buttons
    public function index()
    {
        return view('reports.index');
    }

    // --- Generate PDF ---
    public function generatePdf()
{
    try {
        \Log::info('PDF Generation Started');
        
        // Check if view exists
        if (!view()->exists('reports.export')) {
            \Log::error('View reports.export does not exist!');
            return response('PDF View not found', 500);
        }
        
        $reports = $this->getReportsData();
        \Log::info('Reports data retrieved successfully');
        \Log::info('Report keys: ' . implode(', ', array_keys($reports)));

        // Try to load the view first
        try {
            $view = view('reports.export', compact('reports'))->render();
            \Log::info('View rendered successfully, length: ' . strlen($view));
        } catch (\Exception $e) {
            \Log::error('View rendering error: ' . $e->getMessage());
            throw $e;
        }

        // Now create PDF
        $pdf = PDF::loadView('reports.export', compact('reports'))
                  ->setPaper('a4', 'landscape');
        
        \Log::info('PDF loaded successfully');
        
        $filename = 'Combined-Report_' . now()->format('Ymd_His') . '.pdf';
        \Log::info('Attempting to download PDF: ' . $filename);
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        \Log::error('PDF Generation Error: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        \Log::error('Stack Trace: ' . $e->getTraceAsString());
        
        return response('PDF Generation Error: ' . $e->getMessage(), 500);
    }
}

public function generateExcel()
{
    try {
        \Log::info('Excel Generation Started');
        
        $reports = $this->getReportsData();
        \Log::info('Reports data retrieved successfully');

        // Create CSV content
        $csvContent = '';
        
        // Fleet Performance Section
        $csvContent .= "FLEET PERFORMANCE\n";
        $csvContent .= "Truck ID,Total Pickups,Drivers Present,Issues Reported\n";
        foreach ($reports['fleet'] as $item) {
            $csvContent .= "{$item->truck_id},{$item->total_pickups},{$item->drivers_present},{$item->issues_reported}\n";
        }
        $csvContent .= "\n\n";
        
        // Collection Efficiency Section
        $collection = $reports['collection'];
        $completionRate = $collection->total_pickups > 0 
            ? round(($collection->completed_pickups / $collection->total_pickups) * 100, 2) . '%'
            : '0%';
        
        $csvContent .= "COLLECTION EFFICIENCY\n";
        $csvContent .= "Total Pickups,Completed Pickups,Missed Pickups,Completion Rate\n";
        $csvContent .= ($collection->total_pickups ?? 0) . "," . ($collection->completed_pickups ?? 0) . "," . ($collection->missed_pickups ?? 0) . "," . $completionRate . "\n";
        $csvContent .= "\n\n";
        
        // Resident Issues Section
        $csvContent .= "RESIDENT ISSUES\n";
        $csvContent .= "Issue Type,Other Issue,Location,Incident Date/Time,Description\n"; // Removed Priority
        foreach ($reports['residentIssues'] as $item) {
            $issueType = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->issue_type ?? '');
            $otherIssue = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->other_issue ?? '');
            $location = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->location ?? '');
            $datetime = $item->incident_datetime ?? '';
            $description = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->description ?? '');
            
            $csvContent .= "\"{$issueType}\",\"{$otherIssue}\",\"{$location}\",\"{$datetime}\",\"{$description}\"\n";
        }
        $csvContent .= "\n\n";
        // Driver Issues Section
        $csvContent .= "DRIVER ISSUES\n";
        $csvContent .= "Driver ID,Issue Type,Description,Created At\n";
        foreach ($reports['driverIssues'] as $item) {
            $driverId = $item->driver_id ?? '';
            $issueType = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->issue_type ?? '');
            $description = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->description ?? '');
            $createdAt = $item->created_at ?? '';
            
            $csvContent .= "\"{$driverId}\",\"{$issueType}\",\"{$description}\",\"{$createdAt}\"\n";
        }
        $csvContent .= "\n\n";
        
        // Waste Statistics Section
        $csvContent .= "WASTE STATISTICS\n";
        $csvContent .= "Metric,Value\n";
        $csvContent .= "Today Total (kg)," . ($reports['todayTotal'] ?? 0) . "\n";
        $csvContent .= "Month Total (kg)," . ($reports['monthTotal'] ?? 0) . "\n";
        $csvContent .= "Total Collections," . ($reports['totalCollections'] ?? 0) . "\n";
        $csvContent .= "Trucks Used," . ($reports['environment']->trucks_used ?? 0) . "\n";
        $csvContent .= "Total Pickups (Environment)," . ($reports['environment']->total_pickups ?? 0) . "\n";
        
        \Log::info('CSV content generated, length: ' . strlen($csvContent));
        
        // Set headers for Excel download
        $filename = 'Combined-Report-' . date('Y-m-d-His') . '.csv';
        \Log::info('CSV filename: ' . $filename);
        
        $response = response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        
        \Log::info('Excel generation completed successfully');
        
        return $response;
            
    } catch (\Exception $e) {
        \Log::error('Excel Generation Error: ' . $e->getMessage());
        \Log::error('Stack Trace: ' . $e->getTraceAsString());
        
        return response()->json(['error' => 'Failed to generate Excel: ' . $e->getMessage()], 500);
    }
}

private function getReportsData()
{
    try {
        \Log::info('Starting getReportsData()');
        
        $fleet = DB::table('trucks as t')
            ->leftJoin('pickups as p', 'p.truck_id', '=', 't.id')
            ->leftJoin('attendances as a', 'a.user_id', '=', 't.id')
            ->leftJoin('driver_reports as dr', 'dr.driver_id', '=', 't.id')
            ->select(
                't.id as truck_id',
                DB::raw('COUNT(p.id) as total_pickups'),
                DB::raw('COUNT(DISTINCT a.user_id) as drivers_present'),
                DB::raw('COUNT(DISTINCT dr.id) as issues_reported')
            )
            ->groupBy('t.id')
            ->get();
        
        \Log::info('Fleet data retrieved: ' . $fleet->count() . ' records');

        $collection = DB::table('pickups')
            ->select(
                DB::raw('COUNT(*) as total_pickups'),
                DB::raw("SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed_pickups"),
                DB::raw("SUM(CASE WHEN status='Missed' THEN 1 ELSE 0 END) as missed_pickups")
            )
            ->first();

        // FIXED: Removed 'priority' column that doesn't exist
        $residentIssues = DB::table('reports')
            ->select('issue_type', 'other_issue', 'location', 'incident_datetime', 'description', 'photo_path')
            ->orderBy('incident_datetime', 'desc')
            ->get();
        
        \Log::info('Resident issues retrieved: ' . $residentIssues->count() . ' records');

        $driverIssues = DB::table('driver_reports')
            ->select('driver_id', 'issue_type', 'description', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        \Log::info('Driver issues retrieved: ' . $driverIssues->count() . ' records');

        $environment = DB::table('pickups')
            ->select(
                DB::raw('COUNT(DISTINCT truck_id) as trucks_used'),
                DB::raw('COUNT(*) as total_pickups')
            )
            ->first();

        $todayTotal = DB::table('waste_collections')
                        ->whereDate('pickup_date', now())
                        ->sum('kilos');

        $monthTotal = DB::table('waste_collections')
                        ->whereMonth('pickup_date', now()->month)
                        ->whereYear('pickup_date', now()->year)
                        ->sum('kilos');

        $totalCollections = DB::table('waste_collections')->count();

        $attendance = \App\Models\Attendance::orderBy('pickupSession', 'desc')->get();
        $users = \App\Models\User::all();
        $locations = \App\Models\Location::all();
        
        \Log::info('All report data retrieved successfully');

        return [
            'fleet' => $fleet,
            'collection' => $collection,
            'residentIssues' => $residentIssues,
            'driverIssues' => $driverIssues,
            'environment' => $environment,
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
            'totalCollections' => $totalCollections,
            'attendance' => $attendance,
            'users' => $users,
            'locations' => $locations,
        ];
        
    } catch (\Exception $e) {
        \Log::error('getReportsData Error: ' . $e->getMessage());
        \Log::error('Stack Trace: ' . $e->getTraceAsString());
        throw $e;
    }
}

}