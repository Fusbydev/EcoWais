<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'Barangay Suqui',
            'Barangay San Vicente',
            'Barangay Lalud',
            'Barangay Guinobatan',
            'Barangay Bayanan I',
            'Barangay Lumangbayan'
        ];

        foreach ($locations as $location) {
            DB::table('locations')->insert([
                'location' => $location,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
