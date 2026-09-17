<?php

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;

it('スタッフダッシュボードを表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

    $this->actingAs($user)
        ->get('/staff/dashboard')
        ->assertOk()
        ->assertViewIs('staff.dashboard');
});
