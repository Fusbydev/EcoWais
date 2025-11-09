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

Route::get('barangay-waste-collector/homepage', function () { 
    return view('barangay-waste-collector.homepage');
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

Route::get('municipality-admin/dashboard', function () {  
    return view('municipality-admin.dashboard');
})->name('municipality.dashboard');


Route::get('/shared-view/map', function () {
    return view('shared-view.map');
})->name('map.view');

Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');
