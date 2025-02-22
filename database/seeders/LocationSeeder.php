<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Locations for Zone 1
        $zone1Locations = [
            [
                'shop_name' => 'Shop 1',
                'address' => 'Address 1, Accra',
                'ghana_post_gps_code' => 'GG-739-9069',
                'latitude' => 5.6037,
                'longitude' => -0.1870,
                'contact_number' => '+233200000001',
                'status' => 'active'
            ],
            [
                'shop_name' => 'Shop 2',
                'address' => 'Address 2, Accra',
                'ghana_post_gps_code' => 'GA-492-8024',
                'latitude' => 5.6057,
                'longitude' => -0.1890,
                'contact_number' => '+233200000002',
                'status' => 'active'
            ],
            [
                'shop_name' => 'Shop 3',
                'address' => 'Address 3, Accra',
                'ghana_post_gps_code' => 'GA-123-4567',
                'latitude' => 5.6077,
                'longitude' => -0.1910,
                'contact_number' => '+233200000003',
                'status' => 'active'
            ]
        ];

        foreach ($zone1Locations as $location) {
            Location::create(array_merge($location, ['zone_id' => 1]));
        }

        // Locations for Zone 2
        $zone2Locations = [
            [
                'shop_name' => 'Shop 4',
                'address' => 'Address 4, Tema',
                'ghana_post_gps_code' => 'GT-234-5678',
                'latitude' => 5.7037,
                'longitude' => -0.2870,
                'contact_number' => '+233200000004',
                'status' => 'active'
            ],
            [
                'shop_name' => 'Shop 5',
                'address' => 'Address 5, Tema',
                'ghana_post_gps_code' => 'GT-345-6789',
                'latitude' => 5.7057,
                'longitude' => -0.2890,
                'contact_number' => '+233200000005',
                'status' => 'active'
            ]
        ];

        foreach ($zone2Locations as $location) {
            Location::create(array_merge($location, ['zone_id' => 2]));
        }
    }
}
