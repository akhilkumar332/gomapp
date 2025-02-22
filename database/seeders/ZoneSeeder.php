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
        Zone::create([
            'name' => 'Zone 1',
            'description' => 'Description for Zone 1',
            'status' => 'active',
        ]);

        Zone::create([
            'name' => 'Zone 2',
            'description' => 'Description for Zone 2',
            'status' => 'active',
        ]);
    }
}
