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
use App\Http\Controllers\IssueController;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {

    $user = User::findOrFail($id);

    if (! request()->hasValidSignature()) {
        abort(403, 'Invalid or expired verification link');
    }

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification hash');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return redirect('/login')->with('verified', true);

})->middleware('signed')->name('verification.verify');



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

Route::post('/update-tracking', [TruckController::class, 'updateTracking'])->name('update-tracking');

Route::get('/', function() {
    return view('login'); // make sure login.blade.php exists
})->name('login.page');

// Handle login POST
// Show login page
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Handle login submission
Route::post('/login', [AuthController::class, 'login']);


Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


Route::get('/manage-locations', [LocationController::class, 'manageLocation'])->name('location-manager');


Route::post('/locations/assign-admin', [LocationController::class, 'assignAdmin'])
    ->name('locations.assignAdmin');

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
            $driverModel = $truck->driver ?? null; // Get the driver model

            if ($driver && $driverModel) {
                // Get today's attendance for this driver
                $attendance = Attendance::where('user_id', $driver->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();

                $timeIn = $attendance->time_in ?? '-';
                $timeOut = $attendance->time_out ?? '-';
                $attendanceStatus = $attendance->status ?? 'Not Recorded';

                // Calculate hours worked if both time in/out exist
                $hoursWorked = '-';
                if ($timeIn !== '-' && $timeOut !== '-') {
                    $hoursWorked = \Carbon\Carbon::parse($timeIn)
                        ->diffInHours(\Carbon\Carbon::parse($timeOut));
                }

                $truckData->push([
                    'name' => $driver->name,
                    'role' => $driver->role,
                    'truck_id' => $truck->id,
                    'driver_user_id' => $driver->id, // <-- needed for attendance forms
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'hours_worked' => $hoursWorked,
                    'attendance_status' => $attendanceStatus, // Attendance status
                    'status' => $driverModel->status ?? 'Not Recorded', // Driver status from drivers table
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


Route::patch('/trucks/{id}/idle', [TruckController::class, 'setIdle'])
    ->name('truck.setIdle');


// Add this route in web.php
Route::post('/driver/update-status', function(\Illuminate\Http\Request $request) {
    $request->validate([
        'status' => 'required|in:on-route,break,returning'
    ]);

    $userId = session('user_id'); // or Auth::id()
    
    // Find the driver record for this user
    $driver = \App\Models\Driver::where('user_id', $userId)->first();
    
    if (!$driver) {
        return redirect()->back()->with('statusError', 'Driver not found!');
    }
    
    // Update the driver status
    $driver->status = $request->status;
    $driver->save();
    
    return redirect()->back()->with('statusSuccess', 'Status updated successfully!');
})->name('driver.update.status');

Route::get('/barangay-admin/report', function () {

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

    return view('barangay-admin.report-issue', compact(
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

})->name('barangay.admin.report');

Route::get('/barangay-admin/attendance', function () {

    $userId = session('user_id'); // or Auth::id() if using Laravel Auth

    // All locations (for dropdowns, etc.)
    $locations = Location::all();

    // Get the location managed by this admin
    $selectedLocation = Location::where('adminId', $userId)->first();

    // Initialize truckData collection for the table
    $truckData = collect();

    if ($selectedLocation) {

    $today = now()->toDateString();

    // Get pickups for this location, up to today
    $pickups = Pickup::with(['truck.driver.user'])
        ->where('location_id', $selectedLocation->id)
        ->whereDate('pickup_date', '<=', $today) // include today, exclude future
        ->orderBy('pickup_date', 'desc')
        ->get();

    foreach ($pickups as $pickup) {

    $truck = $pickup->truck;
    $driver = $truck?->driver?->user;

    if ($driver) {
        // Get attendance for this driver on this pickup session
        $attendance = Attendance::where('user_id', $driver->id)
            ->where('pickupSession', $pickup->pickup_date) // use pickupSession, not created_at
            ->latest() // get the most recent record if multiple exist
            ->first();

        $timeIn = $attendance->time_in ?? '-';
        $timeOut = $attendance->time_out ?? '-';
        $status = $attendance->status ?? 'Not Recorded';

        $hoursWorked = '-';
        if ($attendance && $attendance->time_in && $attendance->time_out) {
            $hoursWorked = \Carbon\Carbon::parse($attendance->time_in)
                ->diffInHours(\Carbon\Carbon::parse($attendance->time_out));
        }

        $truckData->push([
            'name' => $driver->name,
            'role' => $driver->role,
            'truck_id' => $truck->truck_id,
            'driver_user_id' => $driver->id,
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'hours_worked' => $hoursWorked,
            'status' => $status,
            'pickup_date' => $pickup->pickup_date,
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

    return view('barangay-admin.attendance', compact(
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

})->name('barangay.admin.attendance');

// routes/web.php
Route::get('/barangay/{id}/trucks', [DriverController::class, 'getTrucks'])->name('barangay.trucks');


Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])->name('attendance.timein');
Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut'])->name('attendance.timeout');


Route::get('barangay-waste-collector/homepage', function () {

    $locations = Location::all();

    $userId = session('user_id');
    $driver = DB::table('drivers')->where('user_id', $userId)->first();

    if (!$driver) {
        return "❌ No driver found for this user.";
    }

    $today = Carbon::now()->toDateString();

    // Fetch pickups along with truck routes and completed points
    $scheduledPickups = DB::table('pickups')
        ->join('trucks', 'pickups.truck_id', '=', 'trucks.id')
        ->join('locations', 'pickups.location_id', '=', 'locations.id')
        ->where('trucks.driver_id', $driver->id)
        ->select(
            'pickups.*',
            'pickups.completed_routes as completed_points',
            'locations.location as location_name',
            'trucks.pickups as truck_pickups'
        )
        ->get();

    // Filter only pickups for today
    $todayPickups = $scheduledPickups->filter(function ($pickup) use ($today) {
        return $pickup->pickup_date === $today;
    });

    // Initialize counters for TODAY'S routes only
    $totalCompleted = 0;
    $totalPending = 0;

    foreach ($scheduledPickups as $pickup) {
        // Ensure pickup_date is in 'Y-m-d' format for JS
        $pickup->pickup_date = Carbon::parse($pickup->pickup_date)->toDateString();

        $truckPoints = json_decode($pickup->truck_pickups, true) ?? [];
        $completedPoints = json_decode($pickup->completed_points ?? '[]', true);

        $pickupPoints = [];

        foreach ($truckPoints as $point) {
            $lat = $point['lat'] ?? null;
            $lng = $point['lng'] ?? null;
            $timeWindow = $point['timeWindow'] ?? null;

            if ($lat && $lng) {
                $isCompleted = collect($completedPoints)->contains(function ($c) use ($lat, $lng) {
                    return isset($c['lat'], $c['lng']) && $c['lat'] === $lat && $c['lng'] === $lng;
                });

                $status = $isCompleted ? 'Completed' : 'Pending';
                $pickupPoints[] = [
                    'lat' => $lat,
                    'lng' => $lng,
                    'timeWindow' => $timeWindow,
                    'status' => $status
                ];

                // Only count if this pickup is for today
                if ($pickup->pickup_date === $today) {
                    if ($isCompleted) {
                        $totalCompleted++;
                    } else {
                        $totalPending++;
                    }
                }
            }
        }

        $pickup->points = $pickupPoints;
    }

    // Pass data to view
    return view('barangay-waste-collector.homepage', compact(
        'locations',
        'scheduledPickups',
        'todayPickups',
        'totalCompleted',
        'totalPending'
    ));
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
Route::get('/reports/generate/pdf', [ReportController::class, 'generatePdf'])->name('reports.generate.pdf');
Route::get('/reports/generate/excel', [ReportController::class, 'generateExcel'])->name('reports.generate.excel');


Route::get('/issues', [IssueController::class, 'getIssues'])->name('issues.get');
Route::post('/issues', [IssueController::class, 'addIssue'])->name('issues.add');
Route::delete('/issues/{id}', [IssueController::class, 'deleteIssue'])->name('issues.delete');

Route::post('/reports/{id}/resolve', [BarangayReportController::class, 'resolve'])->name('reports.resolve');

Route::post('/locations/store', [LocationController::class, 'store'])->name('locations.store');
//municipality-admin
Route::get('municipality-admin/admin', function (\Illuminate\Http\Request $request) {
    $filter = $request->query('filter', 'monthly'); // default monthly

    $drivers = Driver::with('user', 'truck')->get();
    $users = User::all();
    $locations = Location::all();
    $trucks = Truck::all();
    $reports = BarangayReport::all();
    $attendance = Attendance::all();

    // --- Waste Dashboard Stats ---
    $todayTotal = WasteCollection::whereDate('pickup_date', now())->sum('kilos');
    $monthTotal = WasteCollection::whereMonth('pickup_date', now()->month)
                    ->whereYear('pickup_date', now()->year)
                    ->sum('kilos');
    $totalWaste = WasteCollection::sum('kilos');    
    $totalCollections = WasteCollection::count();

    // --- Daily Waste Chart Data ---
    $dailyQuery = WasteCollection::select(
        DB::raw('DATE(pickup_date) as date'),
        DB::raw('SUM(kilos) as total')
    );

    if ($filter === 'today') {
        $dailyQuery->whereDate('pickup_date', now());
    } elseif ($filter === 'weekly') {
        $dailyQuery->whereBetween('pickup_date', [now()->startOfWeek(), now()->endOfWeek()]);
    } else { // monthly
        $dailyQuery->whereMonth('pickup_date', now()->month)
                   ->whereYear('pickup_date', now()->year);
    }

    $dailyDataQuery = $dailyQuery->groupBy('date')->orderBy('date')->get();
    $dailyLabels = $dailyDataQuery->pluck('date');
    $dailyData = $dailyDataQuery->pluck('total');

    // --- Waste by Type (Doughnut Chart) ---
    $typeLabels = ['Plastic', 'Biodegradable', 'Metal', 'Glass'];
    $typeDataQuery = WasteCollection::select('waste_type', DB::raw('SUM(kilos) as total'))
                    ->whereIn('waste_type', $typeLabels);

    if ($filter === 'today') {
        $typeDataQuery->whereDate('pickup_date', now());
    } elseif ($filter === 'weekly') {
        $typeDataQuery->whereBetween('pickup_date', [now()->startOfWeek(), now()->endOfWeek()]);
    } else { // monthly
        $typeDataQuery->whereMonth('pickup_date', now()->month)
                      ->whereYear('pickup_date', now()->year);
    }

    $typeDataQuery = $typeDataQuery->groupBy('waste_type')->get()->keyBy('waste_type');

    $typeData = [];
    foreach ($typeLabels as $label) {
        $typeData[$label] = isset($typeDataQuery[$label]) ? $typeDataQuery[$label]->total : 0;
    }

    return view('municipality-admin.admin', compact(
        'locations', 'drivers', 'trucks', 'reports', 'users',
        'todayTotal', 'monthTotal', 'totalCollections',
        'dailyLabels', 'dailyData', 'typeLabels', 'typeData', 'totalWaste',
        'attendance', 'filter'
    ));
})->name('municipality.admin');


Route::get('municipality-admin/scheduling', function () {
    $trucks = Truck::all();
    $locations = Location::all();
    return view('municipality-admin.scheduling', compact('locations', 'trucks'));
})->name('municipality.scheduling');

Route::get('/attendance/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');
Route::get('/attendance/export/csv', [AttendanceController::class, 'exportCsv'])->name('attendance.export.csv');

Route::post('/trucks', [TruckController::class, 'store'])->name('trucks.store');
Route::post('/pickup', [PickupController::class, 'store'])->name('pickup.store');
Route::get('/municipality-admin/scheduling', [PickupController::class, 'index'])->name('municipality.scheduling');
Route::delete('/pickup/{id}', [PickupController::class, 'destroy'])->name('pickup.destroy');


Route::post('/update-truck-pickups', [TruckController::class, 'updatePickups']);


Route::get('/truck-pickups', [TruckController::class, 'getTruckPickups']);
Route::get('/pickup-locations', [PickupController::class, 'getPickupLocations'])->name('pickup.locations');
Route::put('/trucks/{truck}', [TruckController::class, 'update'])->name('trucks.update');


Route::post('/api/get-route', [TruckController::class, 'getRoute']);
Route::get('municipality-admin/dashboard', function () {  
    return view('municipality-admin.dashboard');
})->name('municipality.dashboard');

Route::get('/driver/pickup-locations', [TruckController::class, 'getDriverPickupAddressesByUser']);


// routes/web.php
Route::post('/pickup/{pickup}/complete-point', [PickupController::class, 'completePoint'])
    ->name('pickup.complete-point');




Route::get('/shared-view/map', function () {
    $userId = session('user_id'); // or Auth::id() if using Laravel auth
    $userRole = DB::table('users')
    ->where('id', $userId)
    ->value('role'); // returns the role as a string

    // Base query
    $trucksQuery = DB::table('trucks')
        ->leftJoin('drivers', 'trucks.driver_id', '=', 'drivers.id')
        ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
        ->select(
            'trucks.*',
            'users.name as driver_name'
        );

    // Filter by collector role
    if ($userRole === 'barangay_waste_collector') {
        // Only trucks assigned to this user
        $trucksQuery->where('drivers.user_id', $userId);
    }

    $trucks = $trucksQuery->get();

    // Fetch all pickups
    $allPickups = DB::table('pickups')->get();

    foreach ($trucks as $truck) {
        $totalNodes = 0;
        $truckPickups = [];
        if ($truck->pickups) {
            $truckPickups = is_array($truck->pickups) ? $truck->pickups : json_decode($truck->pickups, true);
            $totalNodes = count($truckPickups);
        }

        $completedNodes = 0;
        $currentLat = null;
        $currentLng = null;

        foreach ($allPickups as $pickup) {
            if ($pickup->truck_id == $truck->id) {
                $currentLat = $pickup->current_latitude ?? $currentLat;
                $currentLng = $pickup->current_longitude ?? $currentLng;

                if ($pickup->completed_routes) {
                    $completedRoutes = is_array($pickup->completed_routes) ? $pickup->completed_routes : json_decode($pickup->completed_routes, true);
                    $completedNodes += count($completedRoutes);
                }
            }
        }

        $truck->progress = $completedNodes . '/' . $totalNodes;
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
