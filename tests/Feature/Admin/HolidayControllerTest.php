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

test('管理者は休業日編集画面を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $holiday = Holiday::create([
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.holidays.edit', $holiday));

    $response->assertOk()
        ->assertSee('休業日編集')
        ->assertSee('2026-12-31')
        ->assertSee('年末年始休業');
});

test('管理者は休業日を更新できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $holiday = Holiday::create([
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ]);

    $response = $this->actingAs($user)
        ->put(route('admin.holidays.update', $holiday), [
            'date' => '2027-01-02',
            'reason' => '臨時休業',
        ]);

    $response->assertRedirect(route('admin.holidays.index'));

    $this->assertDatabaseHas('holidays', [
        'id' => $holiday->id,
        'date' => '2027-01-02',
        'reason' => '臨時休業',
    ]);
});

test('管理者は休業日を削除できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $holiday = Holiday::create([
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.holidays.destroy', $holiday));

    $response->assertRedirect(route('admin.holidays.index'));

    $this->assertDatabaseMissing('holidays', [
        'id' => $holiday->id,
    ]);
});
