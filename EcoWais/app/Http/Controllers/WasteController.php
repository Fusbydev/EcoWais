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
            'waste_type' => 'required',
            'weight' => 'required|numeric',
            'waste_date' => 'required|date'
        ]);

        try {
            $entry = WasteCollection::create([
                'location_id' => $request->location_id,
                'truck_id' => $request->truck_id ?? null, // optional
                'collector_id' => $request->collector_id,
                'waste_type' => $request->waste_type,
                'kilos' => $request->weight,
                'pickup_date' => $request->waste_date,
            ]);

        } catch (\Exception $e) {

        }

        return back()->with('successWaste', 'Waste entry recorded successfully!');
    }

}
