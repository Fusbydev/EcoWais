<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WasteCollection;
use Carbon\Carbon;

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
                'truck_id' => $request->truck_id, // Add this
                'collector_id' => $request->collector_id,
                'waste_type' => $request->waste_type,
                'kilos' => $request->weight,
                'pickup_date' => $request->waste_date,
            ]);

        } catch (\Exception $e) {
            dd($e);
        }

        return back()->with('successWaste', 'Waste entry recorded successfully!');
    }
}