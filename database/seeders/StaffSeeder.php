<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = app()->environment('production')
            ? env('PAISALON_INITIAL_STAFF_PASSWORD')
            : 'password';

        if (! is_string($password) || $password === '') {
            throw new RuntimeException(
                '本番環境では PAISALON_INITIAL_STAFF_PASSWORD の設定が必要です。'
            );
        }

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
            $user = User::firstOrCreate(
                ['email' => $staffData['email']],
                ['password' => Hash::make($password)],
            );

            $staff = Staff::query()->firstOrNew([
                'user_id' => $user->id,
            ]);

            if (! $staff->exists) {
                $staff->forceFill([
                    'user_id' => $user->id,
                    'name' => $staffData['name'],
                    'role' => $staffData['role'],
                ])->save();
            }
        }
    }
}
