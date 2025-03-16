<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'North Zone',
                'description' => 'Northern region delivery zone',
                'status' => 'active',
                'center_lat' => 40.7128, // Sample latitude
                'center_lng' => -74.0060, // Sample longitude
                'radius' => 10, // Sample radius in kilometers
            ],
            [
                'name' => 'South Zone',
                'description' => 'Southern region delivery zone',
                'status' => 'active',
                'center_lat' => 34.0522, // Sample latitude
                'center_lng' => -118.2437, // Sample longitude
                'radius' => 15, // Sample radius in kilometers
            ],
            [
                'name' => 'Central Zone',
                'description' => 'Central region delivery zone',
                'status' => 'active',
                'center_lat' => 41.8781, // Sample latitude
                'center_lng' => -87.6298, // Sample longitude
                'radius' => 20, // Sample radius in kilometers
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}
