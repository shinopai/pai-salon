<?php

use App\Models\Staff;
use App\Models\User;
use App\Enums\StaffRole;

it('管理者スタッフ一覧を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $admin = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $staffUser = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $staff = Staff::forceCreate([
        'user_id' => $staffUser->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.staffs.index')
    );

    $response->assertOk();

    $response->assertViewIs('admin.staffs.index');

    $response->assertViewHas('staffs', function ($staffs) use ($admin, $staff) {
        return $staffs->contains($admin)
            && $staffs->contains($staff);
    });

    $response->assertSee('管理者スタッフ');
    $response->assertSee('テストスタッフ');
});

test('管理者はスタッフ登録画面を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.staffs.create')
    );

    $response->assertOk();

    $response->assertViewIs('admin.staffs.create');
});

test('管理者はスタッフを登録できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $staffUser = User::factory()->create([
        'email' => 'newstaff@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.staffs.store'),
        [
            'user_id' => $staffUser->id,
            'name' => '新規スタッフ',
            'role' => StaffRole::STAFF->value,
        ]
    );

    $response->assertRedirect(
        route('admin.staffs.index')
    );

    $this->assertDatabaseHas('staffs', [
        'user_id' => $staffUser->id,
        'name' => '新規スタッフ',
        'role' => StaffRole::STAFF->value,
    ]);
});

test('管理者はスタッフ詳細を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $staffUser = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $staff = Staff::forceCreate([
        'user_id' => $staffUser->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.staffs.show', $staff)
    );

    $response->assertOk();

    $response->assertViewIs('admin.staffs.show');

    $response->assertViewHas('staff', $staff);

    $response->assertSee('テストスタッフ');
});
