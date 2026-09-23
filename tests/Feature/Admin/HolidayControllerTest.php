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
