<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF; // barryvdh/laravel-dompdf
use DB;
use Carbon\Carbon;
use App\Models\BarangayReport;

class ReportController extends Controller
{
    // Page with button
    public function index()
    {
        return view('reports.index');
    }

    // Generate PDF
    public function generatePdf()
{
    // --- Fleet Performance (same as before)
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

    // --- Collection Efficiency
    $collection = DB::table('pickups')
        ->select(
            DB::raw('COUNT(*) as total_pickups'),
            DB::raw("SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed_pickups"),
            DB::raw("SUM(CASE WHEN status='Missed' THEN 1 ELSE 0 END) as missed_pickups")
        )
        ->first();

    // --- Resident Issues
    $residentIssues = DB::table('reports') // assuming this is the resident table
        ->select('issue_type','other_issue','location','incident_datetime','priority','description','photo_path')
        ->orderBy('incident_datetime','desc')
        ->get();

    // --- Driver Issues
    $driverIssues = DB::table('driver_reports') // driver table
        ->select('driver_id','issue_type','description','created_at')
        ->orderBy('created_at','desc')
        ->get();

    // --- Environmental Impact
    $environment = DB::table('pickups')
        ->select(
            DB::raw('COUNT(DISTINCT truck_id) as trucks_used'),
            DB::raw('COUNT(*) as total_pickups')
        )
        ->first();

    $reports = [
        'fleet' => $fleet,
        'collection' => $collection,
        'residentIssues' => $residentIssues,
        'driverIssues' => $driverIssues,
        'environment' => $environment
    ];

    $pdf = PDF::loadView('reports.export', compact('reports'));
    return $pdf->download('Combined-Report.pdf');
}
}
