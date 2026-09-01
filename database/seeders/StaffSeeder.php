<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffs = [
            [
                'name' => '佐藤',
                'email' => 'sato@example.com',
                'role' => StaffRole::ADMIN,
            ],
            [
                'name' => '小林',
                'email' => 'kobayashi@example.com',
                'role' => StaffRole::STAFF,
            ],
            [
                'name' => '望月',
                'email' => 'mochizuki@example.com',
                'role' => StaffRole::STAFF,
            ],
            [
                'name' => '一ノ瀬',
                'email' => 'ichinose@example.com',
                'role' => StaffRole::STAFF,
            ],
        ];

        foreach ($staffs as $staffData) {
            $user = User::create([
                'email' => $staffData['email'],
                'password' => Hash::make('password'),
            ]);

            Staff::create([
                'user_id' => $user->id,
                'name' => $staffData['name'],
                'role' => $staffData['role'],
            ]);
        }
    }
}
