<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessHours = [
            [
                'day_of_week' => 0,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
            [
                'day_of_week' => 1,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
            [
                'day_of_week' => 2,
                'open_time' => null,
                'close_time' => null,
            ],
            [
                'day_of_week' => 3,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
            [
                'day_of_week' => 4,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
            [
                'day_of_week' => 5,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
            [
                'day_of_week' => 6,
                'open_time' => '10:00',
                'close_time' => '20:00',
            ],
        ];

        foreach ($businessHours as $businessHour) {
            BusinessHour::create($businessHour);
        }
    }
}
