<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name' => 'カット',
                'duration' => 60,
            ],
            [
                'name' => 'カラー',
                'duration' => 90,
            ],
            [
                'name' => 'パーマ',
                'duration' => 120,
            ],
            [
                'name' => '縮毛矯正・ストレート',
                'duration' => 180,
            ],
            [
                'name' => 'トリートメント',
                'duration' => 30,
            ],
            [
                'name' => 'ヘッドスパ・髪頭皮ケア',
                'duration' => 45,
            ],
            [
                'name' => 'ヘアセット',
                'duration' => 45,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
