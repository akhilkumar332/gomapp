<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,      // Create admin user first
            ZoneSeeder::class,       // Create zones
            LocationSeeder::class,    // Create locations in zones
            DriverSeeder::class,     // Create drivers and assign to zones
            AppSettingSeeder::class, // Create default app settings
        ]);
    }
}
