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

test('管理者はスタッフ編集画面を表示できる', function () {
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
        route('admin.staffs.edit', $staff)
    );

    $response->assertOk();

    $response->assertViewIs('admin.staffs.edit');

    $response->assertViewHas('staff', $staff);

    $response->assertSee('テストスタッフ');
    $response->assertSee('staff@example.com');
    $response->assertSee(StaffRole::STAFF->value);
});

test('管理者はスタッフ情報を更新できる', function () {
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
        'name' => '更新前スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.staffs.update', $staff),
        [
            'name' => '更新後スタッフ',
            'email' => 'updated@example.com',
            'role' => StaffRole::ADMIN->value,
        ]
    );

    $response->assertRedirect(
        route('admin.staffs.show', $staff)
    );

    $this->assertDatabaseHas('staffs', [
        'id' => $staff->id,
        'name' => '更新後スタッフ',
        'role' => StaffRole::ADMIN->value,
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $staffUser->id,
        'email' => 'updated@example.com',
    ]);
});

test('管理者はスタッフ自身の現在のメールアドレスを維持して更新できる', function () {
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
        'name' => '更新前スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.staffs.update', $staff),
        [
            'name' => '更新後スタッフ',
            'email' => 'staff@example.com',
            'role' => StaffRole::STAFF->value,
        ]
    );

    $response->assertRedirect(
        route('admin.staffs.show', $staff)
    );

    $this->assertDatabaseHas('staffs', [
        'id' => $staff->id,
        'name' => '更新後スタッフ',
        'role' => StaffRole::STAFF->value,
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $staffUser->id,
        'email' => 'staff@example.com',
    ]);
});

test('管理者は他のユーザーが使用中のメールアドレスには更新できない', function () {
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
        'name' => '更新前スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $otherUser = User::factory()->create([
        'email' => 'other@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.staffs.update', $staff),
        [
            'name' => '更新後スタッフ',
            'email' => $otherUser->email,
            'role' => StaffRole::STAFF->value,
        ]
    );

    $response->assertSessionHasErrors('email');

    $this->assertDatabaseHas('users', [
        'id' => $staffUser->id,
        'email' => 'staff@example.com',
    ]);
});

test('一般スタッフは管理者スタッフ一覧にアクセスできない', function () {
    $user = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '一般スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.staffs.index')
    );

    $response->assertForbidden();
});

test('管理者は管理者スタッフ一覧にアクセスできる', function () {
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
        route('admin.staffs.index')
    );

    $response->assertOk();
});
