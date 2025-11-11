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
use App\Http\Controllers\PageController;

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
    // You can return a view for the dashboard
    return view('barangay-admin.homepage');
})->name('barangay.admin.homepage');

use Illuminate\Support\Facades\DB;

Route::get('barangay-waste-collector/homepage', function () {

    // Step 1: Get the user_id from the session
    $userId = session('user_id');

    // Step 2: Find the driver for this user
    $driver = DB::table('drivers')->where('user_id', $userId)->first();

    if (!$driver) {
        return "❌ No driver found for this user.";
    }

    // Step 3: Join pickups with trucks and locations
    $scheduledPickups = DB::table('pickups')
        ->join('trucks', 'pickups.truck_id', '=', 'trucks.id')
        ->join('locations', 'pickups.location_id', '=', 'locations.id')
        ->where('trucks.driver_id', $driver->id)
        ->select(
            'pickups.*',
            'locations.location as location_name'
        )
        ->get();

    // Step 4: Return data to the view
    return view('barangay-waste-collector.homepage', [
        'scheduledPickups' => $scheduledPickups,
        'driver' => $driver
    ]);
})->name('barangay.waste.collector.homepage');


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


Route::get('/shared-view/map', function () {
    $trucks = Truck::all();
    return view('shared-view.map', compact('trucks'));
})->name('map.view');

Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');
