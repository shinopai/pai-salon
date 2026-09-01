<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMenus = [
            '佐藤' => [
                'カット',
                'カラー',
                'パーマ',
                '縮毛矯正・ストレート',
                'トリートメント',
                'ヘッドスパ・髪頭皮ケア',
                'ヘアセット',
            ],
            '小林' => [
                'カット',
                'カラー',
                'トリートメント',
                'ヘッドスパ・髪頭皮ケア',
                'ヘアセット',
            ],
            '望月' => [
                'カット',
                'パーマ',
                'トリートメント',
                'ヘッドスパ・髪頭皮ケア',
                'ヘアセット',
            ],
            '一ノ瀬' => [
                'カット',
                '縮毛矯正・ストレート',
                'トリートメント',
                'ヘッドスパ・髪頭皮ケア',
                'ヘアセット',
            ],
        ];

        foreach ($staffMenus as $staffName => $menuNames) {
            $staff = Staff::where('name', $staffName)->firstOrFail();

            foreach ($menuNames as $menuName) {
                $menu = Menu::where('name', $menuName)->firstOrFail();

                $staff->menus()->attach($menu->id);
            }
        }
    }
}
