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
                'name' => 'North Accra',
                'description' => 'Northern region of Accra',
                'boundaries' => json_encode([
                    [5.6500, -0.2000],
                    [5.6500, -0.1500],
                    [5.7000, -0.1500],
                    [5.7000, -0.2000],
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Central Accra',
                'description' => 'Central business district',
                'boundaries' => json_encode([
                    [5.6000, -0.2500],
                    [5.6000, -0.1800],
                    [5.6500, -0.1800],
                    [5.6500, -0.2500],
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'South Accra',
                'description' => 'Southern coastal region',
                'boundaries' => json_encode([
                    [5.5500, -0.2500],
                    [5.5500, -0.1500],
                    [5.6000, -0.1500],
                    [5.6000, -0.2500],
                ]),
                'status' => 'active',
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}
