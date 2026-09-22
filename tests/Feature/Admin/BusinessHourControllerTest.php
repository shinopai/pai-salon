<?php

use App\Enums\StaffRole;
use App\Models\BusinessHour;
use App\Models\Staff;
use App\Models\User;

test('管理者は営業時間一覧を表示できる', function () {
    $user = User::factory()->create();

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    BusinessHour::create([
        'day_of_week' => 0,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    BusinessHour::create([
        'day_of_week' => 1,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    BusinessHour::create([
        'day_of_week' => 2,
        'open_time' => null,
        'close_time' => null,
    ]);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    BusinessHour::create([
        'day_of_week' => 4,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    BusinessHour::create([
        'day_of_week' => 5,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    BusinessHour::create([
        'day_of_week' => 6,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.business-hours.index'));

    $response
        ->assertOk()
        ->assertSee('営業時間管理')
        ->assertSee('日曜日')
        ->assertSee('月曜日')
        ->assertSee('火曜日')
        ->assertSee('水曜日')
        ->assertSee('木曜日')
        ->assertSee('金曜日')
        ->assertSee('土曜日')
        ->assertSee('10:00')
        ->assertSee('20:00')
        ->assertSee('定休日');
});
