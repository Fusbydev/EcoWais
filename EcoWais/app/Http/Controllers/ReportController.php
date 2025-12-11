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
        $reports = $this->getReportsData();

        $pdf = PDF::loadView('reports.export', compact('reports'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Combined-Report_' . now()->format('Ymd_His') . '.pdf');
    } catch (\Exception $e) {
        \Log::error('PDF Generation Error: ' . $e->getMessage());
        return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}


    // --- Generate Excel (Manual CSV) ---
    public function generateExcel()
    {
        try {
            $reports = $this->getReportsData();

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
            $csvContent .= "Issue Type,Other Issue,Location,Incident Date/Time,Priority,Description\n";
            foreach ($reports['residentIssues'] as $item) {
                $issueType = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->issue_type ?? '');
                $otherIssue = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->other_issue ?? '');
                $location = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->location ?? '');
                $datetime = $item->incident_datetime ?? '';
                $priority = $item->priority ?? '';
                $description = str_replace([',', "\n", "\r"], [';', ' ', ' '], $item->description ?? '');
                
                $csvContent .= "\"{$issueType}\",\"{$otherIssue}\",\"{$location}\",\"{$datetime}\",\"{$priority}\",\"{$description}\"\n";
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
            
            // Set headers for Excel download
            $filename = 'Combined-Report-' . date('Y-m-d-His') . '.csv';
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Exception $e) {
            \Log::error('Excel Generation Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    // --- Helper: Gather all report data ---
    private function getReportsData()
{
    // --- Existing reports ---
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

    $collection = DB::table('pickups')
        ->select(
            DB::raw('COUNT(*) as total_pickups'),
            DB::raw("SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed_pickups"),
            DB::raw("SUM(CASE WHEN status='Missed' THEN 1 ELSE 0 END) as missed_pickups")
        )
        ->first();

    $residentIssues = DB::table('reports')
        ->select('issue_type','other_issue','location','incident_datetime','priority','description','photo_path')
        ->orderBy('incident_datetime','desc')
        ->get();

    $driverIssues = DB::table('driver_reports')
        ->select('driver_id','issue_type','description','created_at')
        ->orderBy('created_at','desc')
        ->get();

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

    // --- Attendance data ---
    $attendance = \App\Models\Attendance::orderBy('pickupSession', 'desc')->get();
    $users = \App\Models\User::all();
    $locations = \App\Models\Location::all();

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
}

}