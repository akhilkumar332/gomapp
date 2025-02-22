<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DriverZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create drivers
        $drivers = [
            [
                'name' => 'Driver 1',
                'email' => 'driver1@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+233200000001',
                'role' => 'driver'
            ],
            [
                'name' => 'Driver 2',
                'email' => 'driver2@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '+233200000002',
                'role' => 'driver'
            ]
        ];

        foreach ($drivers as $driver) {
            $user = User::create($driver);
            
            // Assign Driver 1 to Zone 1
            if ($user->phone_number === '+233200000001') {
                DriverZone::create([
                    'driver_id' => $user->id,
                    'zone_id' => 1
                ]);
            }
            
            // Assign Driver 2 to Zone 2
            if ($user->phone_number === '+233200000002') {
                DriverZone::create([
                    'driver_id' => $user->id,
                    'zone_id' => 2
                ]);
            }
        }
    }
}
