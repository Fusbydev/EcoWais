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
use App\Http\Controllers\PageController;
use App\Http\Controllers\DriverReportController;
use App\Http\Controllers\AttendanceController;

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


Route::get('/', function() {
    return view('login'); // make sure login.blade.php exists
})->name('login.page');

// Handle login POST
Route::post('/login', [AuthController::class, 'login'])->name('login');



Route::get('/barangay-admin/homepage', function () {

    $locations = Location::all();
    $barangayId = request('barangay');
    $attendance = Attendance::all();

    //get the attendances status total number, absent, present, late
    $total = $attendance->count();
    $absent = $attendance->where('status', 'Absent')->count();
    $present = $attendance->where('status', 'Present')->count();
    $late = $attendance->where('status', 'Late')->count();

    $selectedLocation = null;
    $trucks = collect(); // empty by default
    $collectors = collect();

    if ($barangayId) {
        $selectedLocation = Location::find($barangayId);

        // trucks assigned to this barangay
        $trucks = Truck::with(['driver.user'])
            ->where('initial_location', $selectedLocation->location)
            ->get();

        // collectors assigned to this barangay
        $collectors = $selectedLocation->collectors ?? collect();
    }

    return view('barangay-admin.homepage', compact(
        'locations',
        'selectedLocation',
        'trucks',
        'collectors',
        "absent",
        "present",
        "late"
    ));
})->name('barangay.admin.homepage');

// routes/web.php
Route::get('/barangay/{id}/trucks', [DriverController::class, 'getTrucks'])->name('barangay.trucks');


Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.timein');
Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.timeout');

use Illuminate\Support\Facades\DB;

Route::get('barangay-waste-collector/homepage', function () {

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


    return view('barangay-waste-collector.homepage', [
        'scheduledPickups' => $scheduledPickups,
        'driver' => $driver
    ]);
})->name('barangay.waste.collector.homepage');

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





//municipality-admin
Route::get('municipality-admin/admin', function () {
    $drivers = Driver::all();
    $locations = Location::all();
    $trucks = Truck::all();
    return view('municipality-admin.admin', compact('locations', 'drivers', 'trucks'));
})->name('municipality.admin');

Route::get('municipality-admin/scheduling', function () {
    $trucks = Truck::all();
    $locations = Location::all();
    return view('municipality-admin.scheduling', compact('locations', 'trucks'));
})->name('municipality.scheduling');

Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
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

Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');
