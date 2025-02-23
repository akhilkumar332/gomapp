<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Zone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = Zone::all();
        $drivers = User::where('role', 'driver')->get();

        foreach ($zones as $zone) {
            // Create 5-8 locations per zone
            $numLocations = rand(5, 8);
            
            for ($i = 0; $i < $numLocations; $i++) {
                $status = rand(0, 1) ? 'completed' : 'active';
                $completed = $status === 'completed';
                $driver = $drivers->random();
                
                $location = [
                    'zone_id' => $zone->id,
                    'shop_name' => 'Shop ' . fake()->company(),
                    'address' => fake()->address(),
                    'ghana_post_gps_code' => 'GA-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'latitude' => fake()->latitude(5.55, 5.70),
                    'longitude' => fake()->longitude(-0.25, -0.15),
                    'contact_number' => '020' . rand(1000000, 9999999),
                    'status' => $status,
                    'priority' => rand(1, 5),
                ];

                if ($completed) {
                    $completedAt = Carbon::now()->subHours(rand(1, 72));
                    $location = array_merge($location, [
                        'started_at' => $completedAt->copy()->subMinutes(rand(15, 120)),
                        'completed_at' => $completedAt,
                        'completed_by' => $driver->id,
                        'payment_received' => rand(0, 1),
                        'payment_amount_received' => rand(50, 500),
                    ]);
                }

                Location::create($location);
            }
        }
    }
}
