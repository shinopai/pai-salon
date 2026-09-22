<?php

use App\Enums\StaffRole;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;

test('管理者はメニュー一覧を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.menus.index')
    );

    $response->assertOk();

    $response->assertViewIs('admin.menus.index');

    $response->assertViewHas('menus', function ($menus) use ($menu) {
        return $menus->contains($menu);
    });

    $response->assertSee('カット');
    $response->assertSee('60分');
});

test('管理者はメニュー登録画面を表示できる', function () {
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
        route('admin.menus.create')
    );

    $response->assertOk();

    $response->assertViewIs('admin.menus.create');

    $response->assertSee('メニュー登録');
    $response->assertSee('メニュー名');
    $response->assertSee('所要時間');
});

test('管理者はメニューを登録できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => '新メニュー',
            'duration' => 90,
        ]
    );

    $response->assertRedirect(
        route('admin.menus.index')
    );

    $this->assertDatabaseHas('menus', [
        'name' => '新メニュー',
        'duration' => 90,
    ]);
});

test('メニュー名が未入力の場合はメニューを登録できない', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => '',
            'duration' => 60,
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('menus', [
        'duration' => 60,
    ]);
});

test('メニュー名が101文字以上の場合はメニューを登録できない', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => str_repeat('あ', 101),
            'duration' => 60,
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseMissing('menus', [
        'duration' => 60,
    ]);
});

test('所要時間が未入力の場合はメニューを登録できない', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => '新メニュー',
            'duration' => '',
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseMissing('menus', [
        'name' => '新メニュー',
    ]);
});

test('所要時間が整数でない場合はメニューを登録できない', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => '新メニュー',
            'duration' => '60.5',
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseMissing('menus', [
        'name' => '新メニュー',
    ]);
});

test('所要時間が1未満の場合はメニューを登録できない', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('admin.menus.store'),
        [
            'name' => '新メニュー',
            'duration' => 0,
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseMissing('menus', [
        'name' => '新メニュー',
    ]);
});

test('管理者はメニュー詳細を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.menus.show', $menu)
    );

    $response->assertOk();

    $response->assertViewIs('admin.menus.show');

    $response->assertViewHas('menu', $menu);

    $response->assertSee('カット');
    $response->assertSee('60分');
});
