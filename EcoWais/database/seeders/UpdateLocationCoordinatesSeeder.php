<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class UpdateLocationCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            'Barangay Suqui' => ['latitude' => '13.4177', 'longitude' => '121.2040'],
            'Barangay San Vicente' => ['latitude' => '13.4120', 'longitude' => '121.1787'],
            'Barangay Lalud' => ['latitude' => '13.3993', 'longitude' => '121.1739'],
            'Barangay Bayanan I' => ['latitude' => '13.3679', 'longitude' => '121.1685'],
            'Barangay Lumangbayan' => ['latitude' => '13.4009', 'longitude' => '121.1816'],
            'Barangay Guinobatan' => ['latitude' => '13.4200', 'longitude' => '121.1950'],
        ];

        foreach ($locations as $name => $coords) {
            Location::where('location', $name)->update($coords);
        }

        $this->command->info('✅ Barangay coordinates successfully updated!');
    }
}
