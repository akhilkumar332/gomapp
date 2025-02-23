<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'John Driver',
                'email' => 'john@driver.com',
                'password' => Hash::make('password123'),
                'phone_number' => '0201234567',
                'role' => 'driver',
                'status' => 'active',
                'phone_verified' => true,
                'phone_verified_at' => now(),
                'last_latitude' => 5.6500,
                'last_longitude' => -0.1870,
                'last_location_update' => Carbon::now(),
            ],
            [
                'name' => 'David Driver',
                'email' => 'david@driver.com',
                'password' => Hash::make('password123'),
                'phone_number' => '0207654321',
                'role' => 'driver',
                'status' => 'active',
                'phone_verified' => true,
                'phone_verified_at' => now(),
                'last_latitude' => 5.5800,
                'last_longitude' => -0.2100,
                'last_location_update' => Carbon::now(),
            ],
            [
                'name' => 'Sarah Driver',
                'email' => 'sarah@driver.com',
                'password' => Hash::make('password123'),
                'phone_number' => '0209876543',
                'role' => 'driver',
                'status' => 'active',
                'phone_verified' => true,
                'phone_verified_at' => now(),
                'last_latitude' => 5.6200,
                'last_longitude' => -0.1950,
                'last_location_update' => Carbon::now(),
            ],
        ];

        // Create drivers and assign zones
        foreach ($drivers as $driverData) {
            $driver = User::create($driverData);
            
            // Assign random zones to each driver (1-2 zones)
            $zoneIds = Zone::inRandomOrder()->take(rand(1, 2))->pluck('id');
            foreach ($zoneIds as $zoneId) {
                \DB::table('driver_zones')->insert([
                    'driver_id' => $driver->id,
                    'zone_id' => $zoneId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
