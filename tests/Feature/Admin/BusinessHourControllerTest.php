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
        ->assertSee('name="day_of_week"', false)
        ->assertSee('name="open_time"', false)
        ->assertSee('name="close_time"', false)
        ->assertSee('type="submit"', false)
        ->assertSee('更新');
});

test('管理者は曜日別の営業時間を更新できる', function () {
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

    $response = $this->actingAs($user)
        ->put(route('admin.business-hours.update'), [
            'day_of_week' => 0,
            'open_time' => '09:00',
            'close_time' => '18:00',
        ]);

    $response
        ->assertRedirect(route('admin.business-hours.index'));

    expect(BusinessHour::where('day_of_week', 0)->first())
        ->open_time->toBe('09:00')
        ->close_time->toBe('18:00');

    expect(BusinessHour::where('day_of_week', 1)->first())
        ->open_time->toBe('10:00')
        ->close_time->toBe('20:00');
});

test('管理者は火曜日を定休日として更新できる', function () {
    $user = User::factory()->create();

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    BusinessHour::create([
        'day_of_week' => 2,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $response = $this->actingAs($user)
        ->put(route('admin.business-hours.update'), [
            'day_of_week' => 2,
            'open_time' => null,
            'close_time' => null,
        ]);

    $response
        ->assertRedirect(route('admin.business-hours.index'));

    $businessHour = BusinessHour::where('day_of_week', 2)->first();

    expect($businessHour->open_time)->toBeNull()
        ->and($businessHour->close_time)->toBeNull();
});
