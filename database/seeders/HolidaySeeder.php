<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Holiday::create([
            'date' => '2026-10-12',
            'reason' => '臨時休業',
        ]);

        Holiday::create([
            'date' => '2026-11-03',
            'reason' => '祝日休業',
        ]);

        Holiday::create([
            'date' => '2026-11-23',
            'reason' => '祝日休業',
        ]);

        Holiday::create([
            'date' => '2026-12-29',
            'reason' => '年末休業',
        ]);

        Holiday::create([
            'date' => '2026-12-30',
            'reason' => '年末休業',
        ]);
    }
}
