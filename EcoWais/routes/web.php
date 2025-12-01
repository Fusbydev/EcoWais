<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PickUpController;
use App\Http\Controllers\TruckController;
use App\Models\Location;
use App\Models\Driver;
use App\Models\Pickup;
use App\Models\Truck;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WasteCollection;
use App\Models\BarangayReport;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DriverReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BarangayReportController;
use App\Http\Controllers\WasteController;
use Carbon\Carbon;
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/tracking', [PageController::class, 'tracking'])->name('tracking');

Route::middleware(['auth', 'role:municipality_admin'])->group(function () {
    Route::get('/admin', [PageController::class, 'admin'])->name('admin');
    Route::get('/driver', [PageController::class, 'driver'])->name('driver');
    Route::get('/resident', [PageController::class, 'resident'])->name('resident');
    Route::get('/admin-barangay', [PageController::class, 'barangayScheduling'])->name('admin-barangay');
});

Route::middleware(['auth', 'role:barangay_admin'])->group(function () {
    Route::get('/barangay/resident', [PageController::class, 'resident'])->name('barangay.resident');
    Route::get('/barangay/scheduling', [PageController::class, 'barangayScheduling'])->name('barangay.scheduling');
});

Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver/dashboard', [PageController::class, 'driver'])->name('driver.dashboard');
});
// Show user management page
Route::get('/user-management', [UserController::class, 'index'])->name('user-management');

// Add new user
Route::post('/users', [UserController::class, 'store'])->name('users.store');

// Edit/update user
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

// Activate user
Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');

// Deactivate user
Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');


Route::get('/', function() {
    return view('login'); // make sure login.blade.php exists
})->name('login.page');

// Handle login POST
Route::post('/login', [AuthController::class, 'login'])->name('login');


Route::get('/barangay-admin/homepage', function () {

    $userId = session('user_id'); // or Auth::id() if using Laravel Auth

    // All locations (for dropdowns, etc.)
    $locations = Location::all();

    // Get the location managed by this admin
    $selectedLocation = Location::where('adminId', $userId)->first();

    // Initialize truckData collection for the table
    $truckData = collect();

    if ($selectedLocation) {
        
         $sessionPickups = Pickup::where('location_id', $selectedLocation->id)
    ->orderBy('pickup_date', 'asc')
    ->pluck('pickup_date'); // <-- use pluck to get only the dates

        // Get trucks assigned to this location with drivers
        $trucks = Truck::with('driver.user')
            ->where('initial_location', $selectedLocation->location)
            ->get();

        foreach ($trucks as $truck) {
            $driver = $truck->driver->user ?? null;

            if ($driver) {
                // Get today's attendance for this driver
                $attendance = Attendance::where('user_id', $driver->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();

                $timeIn = $attendance->time_in ?? '-';
                $timeOut = $attendance->time_out ?? '-';
                $status = $attendance->status ?? 'Not Recorded';

                // Calculate hours worked if both time in/out exist
                $timeIn = $attendance->time_in ?? null;
                $timeOut = $attendance->time_out ?? null;
                $status = $attendance->status ?? 'Not Recorded';

                $hoursWorked = '-';
                if ($timeIn && $timeOut) {
                    $hoursWorked = \Carbon\Carbon::parse($timeIn)
                        ->diffInHours(\Carbon\Carbon::parse($timeOut));
                }


                $truckData->push([
                    'name' => $driver->name,
                    'role' => $driver->role,
                    'truck_id' => $truck->id,
                    'driver_user_id' => $driver->id, // <-- needed for attendance forms
                    'time_in' => $timeIn ?? '-',
                    'time_out' => $timeOut ?? '-',
                    'hours_worked' => $hoursWorked,
                    'status' => $status,
                    'sessionPickups' => $sessionPickups
                ]);


            }
        }
    }

    // Attendance stats
    $attendance = Attendance::all();
    $total = $attendance->count();
    $location = Location::where('adminId', $userId)->first();

if ($location) {
    // Filter attendances by location_id
    $attendances = Attendance::where('location_id', $location->id)->get();

    $absent  = $attendances->where('status', 'Absent')->count();
    $present = $attendances->where('status', 'Present')->count();
    $late    = $attendances->where('status', 'Late')->count();
} else {
    $absent = $present = $late = 0; // no location assigned
}

    // Collectors (all trucks for now)
    $collectors1 = Truck::all();

    // Reports with driver information
    $reports = BarangayReport::with('driver.user')
                ->where('adminId', $userId)
                ->orderBy('incident_datetime', 'desc')
                ->get();

    // Get pickups for this admin's location
    $pickupDates = collect();
    if ($selectedLocation) {
        $pickupDates = Pickup::where('location_id', $selectedLocation->id)
            ->pluck('pickup_date');
    }

return view('barangay-admin.homepage', compact(
    'locations',
    'selectedLocation',
    'truckData',
    'absent',
    'present',
    'late',
    'reports',
    'collectors1',
    'pickupDates',
));

})->name('barangay.admin.homepage');



// routes/web.php
Route::get('/barangay/{id}/trucks', [DriverController::class, 'getTrucks'])->name('barangay.trucks');


Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.timein');
Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.timeout');

use Illuminate\Support\Facades\DB;

Route::get('barangay-waste-collector/homepage', function () {

    $locations = Location::all();

    $userId = session('user_id');
    $driver = DB::table('drivers')->where('user_id', $userId)->first();

    if (!$driver) {
        return "❌ No driver found for this user.";
    }

    $scheduledPickups = DB::table('pickups')
        ->join('trucks', 'pickups.truck_id', '=', 'trucks.id')
        ->join('locations', 'pickups.location_id', '=', 'locations.id')
        ->where('trucks.driver_id', $driver->id)
        ->select(
            'pickups.*',
            'locations.location as location_name',
            'trucks.pickups as truck_pickups'
        )
        ->get();

    foreach ($scheduledPickups as $pickup) {
        $decoded = json_decode($pickup->truck_pickups, true);
        $pickupPoints = [];

        if (is_array($decoded)) {
            foreach ($decoded as $point) {
                if (isset($point['lat'], $point['lng'])) {
                    $pickupPoints[] = [
                        'lat' => $point['lat'],
                        'lng' => $point['lng'],
                        'timeWindow' => $point['timeWindow'] ?? null
                    ];
                }
            }
        }

        $pickup->points = $pickupPoints;
    }

    // Waste totals for dashboard
    $today = Carbon::now()->toDateString();
    $month = Carbon::now()->format('Y-m');

    $todayTotal = WasteCollection::whereDate('pickup_date', $today)
        ->sum('kilos');

    $monthTotal = WasteCollection::where('pickup_date', 'like', "$month%")
        ->sum('kilos');

    return view('barangay-waste-collector.homepage', [
        'scheduledPickups' => $scheduledPickups,
        'driver' => $driver,
        'locations' => $locations,
        'todayTotal' => $todayTotal,
        'monthTotal' => $monthTotal
    ]);
})->name('barangay.waste.collector.homepage');


Route::post('/waste/save', [WasteController::class, 'store'])->name('waste.store');


Route::post('pickup/{pickup}/complete-point', function (Request $request, $pickup) {
    $request->validate([
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
    ]);

    $pickupRecord = DB::table('pickups')->where('id', $pickup)->first();
    if (!$pickupRecord) {
        return response()->json(['success' => false, 'message' => 'Pickup not found'], 404);
    }

    // Decode existing completed_routes JSON
    $completedRoutes = json_decode($pickupRecord->completed_routes ?? '[]', true);

    // Add the new point if it doesn't exist
    $exists = collect($completedRoutes)->contains(function($route) use ($request) {
        $tolerance = 0.00001;
        return abs($route['lat'] - $request->lat) < $tolerance && abs($route['lng'] - $request->lng) < $tolerance;
    });

    if (!$exists) {
        $completedRoutes[] = [
            'lat' => $request->lat,
            'lng' => $request->lng,
        ];

        DB::table('pickups')->where('id', $pickup)->update([
            'completed_routes' => json_encode($completedRoutes),
            'current_latitude' => $request->lat,
            'current_longitude' => $request->lng,
            'updated_at' => now(),
        ]);
    }

    return response()->json(['success' => true, 'completed_routes' => $completedRoutes]);
});

Route::get('/barangay/{id}/collectors', [DriverController::class, 'getCollectors']);
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');


Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/generate', [ReportController::class, 'generatePdf'])->name('reports.generate.pdf');


Route::post('/locations/store', [LocationController::class, 'store'])->name('locations.store');
//municipality-admin
Route::get('municipality-admin/admin', function () {
    $drivers = Driver::with('user', 'truck')->get();
    $users = User::all();
    $locations = Location::all();
    $trucks = Truck::all();
    $reports = BarangayReport::all();

    // --- Waste Dashboard Data ---
    $todayTotal = WasteCollection::whereDate('pickup_date', now())->sum('kilos');

    $monthTotal = WasteCollection::whereMonth('pickup_date', now()->month)
                    ->whereYear('pickup_date', now()->year)
                    ->sum('kilos');

    $totalCollections = WasteCollection::count();

    // Daily waste for current month (Line chart)
    $dailyDataQuery = WasteCollection::select(
        DB::raw('DATE(pickup_date) as date'),
        DB::raw('SUM(kilos) as total')
    )
    ->whereMonth('pickup_date', now()->month)
    ->whereYear('pickup_date', now()->year)
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    $dailyLabels = $dailyDataQuery->pluck('date');
    $dailyData = $dailyDataQuery->pluck('total');

    // Waste by type (Doughnut chart)
    $typeLabels = ['Plastic', 'Biodegradable', 'Metal', 'Glass'];

$typeDataQuery = WasteCollection::select(
    'waste_type',
    DB::raw('SUM(kilos) as total')
)
->whereIn('waste_type', $typeLabels)
->groupBy('waste_type')
->get()
->keyBy('waste_type');

$typeData = [];
foreach ($typeLabels as $label) {
    $typeData[$label] = isset($typeDataQuery[$label]) ? $typeDataQuery[$label]->total : 0;
}


    return view('municipality-admin.admin', compact(
        'locations', 'drivers', 'trucks', 'reports', 'users',
        'todayTotal', 'monthTotal', 'totalCollections',
        'dailyLabels', 'dailyData', 'typeLabels', 'typeData'
    ));
})->name('municipality.admin');

Route::get('municipality-admin/scheduling', function () {
    $trucks = Truck::all();
    $locations = Location::all();
    return view('municipality-admin.scheduling', compact('locations', 'trucks'));
})->name('municipality.scheduling');

Route::post('/trucks', [TruckController::class, 'store'])->name('trucks.store');
Route::post('/pickup', [PickupController::class, 'store'])->name('pickup.store');
Route::get('/municipality-admin/scheduling', [PickupController::class, 'index'])->name('municipality.scheduling');
Route::delete('/pickup/{id}', [PickupController::class, 'destroy'])->name('pickup.destroy');
Route::get('/pickup-locations', [PickupController::class, 'getPickupLocations'])->name('pickup.locations');

Route::post('/update-truck-pickups', [TruckController::class, 'updatePickups']);
Route::get('/truck-pickups', [TruckController::class, 'getTruckPickups']);
Route::post('/api/get-route', [TruckController::class, 'getRoute']);
Route::get('municipality-admin/dashboard', function () {  
    return view('municipality-admin.dashboard');
})->name('municipality.dashboard');

Route::get('/driver/pickup-locations', [TruckController::class, 'getDriverPickupAddressesByUser']);

Route::post('/update-driver-status', [TruckController::class, 'updateDriverStatus']);

// routes/web.php
Route::post('/pickup/{pickup}/complete-point', [PickupController::class, 'completePoint'])
    ->name('pickup.complete-point');



Route::get('/shared-view/map', function () {
    $trucks = DB::table('trucks')
        ->leftJoin('drivers', 'trucks.driver_id', '=', 'drivers.id')
        ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
        ->select(
            'trucks.*',
            'users.name as driver_name'
        )
        ->get();

    // Fetch all pickups
    $allPickups = DB::table('pickups')->get();

    foreach ($trucks as $truck) {
        // Total pickups in truck JSON
        $totalNodes = 0;
        $truckPickups = [];
        if ($truck->pickups) {
            $truckPickups = is_array($truck->pickups) ? $truck->pickups : json_decode($truck->pickups, true);
            $totalNodes = count($truckPickups);
        }

        // Completed pickups for this truck
        $completedNodes = 0;
        $currentLat = null;
        $currentLng = null;

        foreach ($allPickups as $pickup) {
            if ($pickup->truck_id == $truck->id) {
                // Get current latitude/longitude (latest pickup)
                $currentLat = $pickup->current_latitude ?? $currentLat;
                $currentLng = $pickup->current_longitude ?? $currentLng;

                // Count completed routes
                if ($pickup->completed_routes) {
                    $completedRoutes = is_array($pickup->completed_routes) ? $pickup->completed_routes : json_decode($pickup->completed_routes, true);
                    $completedNodes += count($completedRoutes);
                }
            }
        }

        $truck->progress = $completedNodes . '/' . $totalNodes; // e.g., 2/5
        $truck->current_latitude = $currentLat;
        $truck->current_longitude = $currentLng;
    }

    return view('shared-view.map', compact('trucks'));
})->name('map.view');

Route::post('/driver/reports', [DriverReportController::class, 'store'])->name('driver.reports.store');

Route::post('/barangay-admin/report/store', [BarangayReportController::class, 'store'])
    ->name('report.store');

Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');
