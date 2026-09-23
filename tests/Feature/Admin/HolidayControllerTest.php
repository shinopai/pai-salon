<?php

use App\Enums\StaffRole;
use App\Models\Holiday;
use App\Models\Staff;
use App\Models\User;

test('管理者は休業日一覧を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    Holiday::create([
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.holidays.index'));

    $response->assertOk()
        ->assertSee('2026-12-31')
        ->assertSee('年末年始休業');
});

test('管理者は休業日登録画面を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $response = $this->actingAs($user)
        ->get(route('admin.holidays.create'));

    $response->assertOk()
        ->assertSee('休業日登録')
        ->assertSee('休業日')
        ->assertSee('理由');
});

test('管理者は休業日を登録できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $response = $this->actingAs($user)
        ->post(route('admin.holidays.store'), [
            'date' => '2026-12-31',
            'reason' => '年末年始休業',
        ]);

    $response->assertRedirect(route('admin.holidays.index'));

    $this->assertDatabaseHas('holidays', [
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ]);
});
