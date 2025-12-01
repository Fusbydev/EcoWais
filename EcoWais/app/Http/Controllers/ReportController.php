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

      // --- Waste Dashboard Stats ---
    $todayTotal = DB::table('waste_collections')
                    ->whereDate('pickup_date', now())
                    ->sum('kilos');

    $monthTotal = DB::table('waste_collections')
                    ->whereMonth('pickup_date', now()->month)
                    ->whereYear('pickup_date', now()->year)
                    ->sum('kilos');

    $totalCollections = DB::table('waste_collections')->count();

    // --- Waste by Type ---
    $typeDataQuery = DB::table('waste_collections')
                    ->select('waste_type', DB::raw('SUM(kilos) as total'))
                    ->groupBy('waste_type')
                    ->pluck('total','waste_type')
                    ->toArray();

    $typeLabels = ['Plastic', 'Biodegradable', 'Metal', 'Glass'];
    $typeData = [];
    foreach ($typeLabels as $label) {
        $typeData[] = $typeDataQuery[$label] ?? 0;
    }

    // --- Daily Waste for the Month ---
    $dailyDataQuery = DB::table('waste_collections')
                    ->select(DB::raw('DATE(pickup_date) as date'), DB::raw('SUM(kilos) as total'))
                    ->whereMonth('pickup_date', now()->month)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

    $dailyLabels = $dailyDataQuery->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
    $dailyData = $dailyDataQuery->pluck('total')->toArray();

    // --- Generate chart images using QuickChart ---
// Daily Waste Chart
$dailyChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
    'type' => 'line',
    'data' => [
        'labels' => $dailyLabels, // array of dates
        'datasets' => [
            [
                'label' => 'Daily Waste (kg)',
                'data' => $dailyData, // array of daily totals
                'borderColor' => 'rgb(54, 162, 235)',
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
            ],
        ],
    ],
]));

// Waste by Type Chart
$typeChartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
    'type' => 'doughnut',
    'data' => [
        'labels' => $typeLabels, // ['Plastic','Biodegradable','Metal','Glass']
        'datasets' => [
            [
                'data' => $typeData, // corresponding kg totals
                'backgroundColor' => ['#36a2eb','#4bc0c0','#ffcd56','#ff6384'],
            ],
        ],
    ],
]));

    $reports = [
        'fleet' => $fleet,
        'collection' => $collection,
        'residentIssues' => $residentIssues,
        'driverIssues' => $driverIssues,
        'environment' => $environment,
        'todayTotal' => $todayTotal,
        'monthTotal' => $monthTotal,
        'totalCollections' => $totalCollections,
        'dailyChartUrl' => $dailyChartUrl,
        'typeChartUrl' => $typeChartUrl
    ];

    $pdf = PDF::loadView('reports.export', compact('reports'));
    return $pdf->download('Combined-Report.pdf');
}
}
