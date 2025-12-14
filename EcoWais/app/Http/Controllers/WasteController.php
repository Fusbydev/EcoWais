<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WasteCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class WasteController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'location_id' => 'required',
        'collector_id' => 'required',
        'truck_id' => 'required|exists:trucks,id',
        'waste_type' => 'required',
        'weight' => 'required|numeric',
        'waste_date' => 'required|date'
    ]);

    try {
        $entry = WasteCollection::create([
            'location_id' => $request->location_id,
            'truck_id' => $request->truck_id,
            'collector_id' => $request->collector_id,
            'waste_type' => $request->waste_type,
            'kilos' => $request->weight,
            'pickup_date' => $request->waste_date,
        ]);

        return back()->with('successWaste', 'Waste entry recorded successfully!');

    } catch (\Exception $e) {
        // Log the error
        Log::error('Waste entry failed', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id() ?? 'guest',
            'request_data' => $request->all()
        ]);

        // Show a generic error message to the user
        return back()->with('errorWaste', 'Failed to save waste entry. Please try again.');
    }
}
}