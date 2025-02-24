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
            ],
            [
                'name' => 'South Zone',
                'description' => 'Southern region delivery zone',
                'status' => 'active',
            ],
            [
                'name' => 'Central Zone',
                'description' => 'Central region delivery zone',
                'status' => 'active',
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}
