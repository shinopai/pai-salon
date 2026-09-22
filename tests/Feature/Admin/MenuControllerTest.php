<?php

use App\Enums\StaffRole;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use App\Enums\ReservationStatus;

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

test('管理者はメニュー編集画面を表示できる', function () {
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
        route('admin.menus.edit', $menu)
    );

    $response->assertOk();

    $response->assertViewIs('admin.menus.edit');

    $response->assertViewHas('menu', $menu);

    $response->assertSee('カット');
    $response->assertSee('60');
});

test('管理者はメニューを更新できる', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => 'カット＋トリートメント',
            'duration' => 90,
        ]
    );

    $response->assertRedirect(
        route('admin.menus.show', $menu)
    );

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット＋トリートメント',
        'duration' => 90,
    ]);
});

test('メニュー更新時にメニュー名が未入力の場合は更新できない', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => '',
            'duration' => 90,
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット',
        'duration' => 60,
    ]);
});

test('メニュー更新時にメニュー名が101文字以上の場合は更新できない', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => str_repeat('あ', 101),
            'duration' => 90,
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット',
        'duration' => 60,
    ]);
});

test('メニュー更新時に所要時間が未入力の場合は更新できない', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => '新カット',
            'duration' => '',
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット',
        'duration' => 60,
    ]);
});

test('メニュー更新時に所要時間が整数でない場合は更新できない', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => '新カット',
            'duration' => '90.5',
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット',
        'duration' => 60,
    ]);
});

test('メニュー更新時に所要時間が1未満の場合は更新できない', function () {
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

    $response = $this->put(
        route('admin.menus.update', $menu),
        [
            'name' => '新カット',
            'duration' => 0,
        ]
    );

    $response->assertSessionHasErrors('duration');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => 'カット',
        'duration' => 60,
    ]);
});

test('管理者は予約がないメニューを削除できる', function () {
    $user = User::factory()->create();

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response
        ->assertRedirect(route('admin.menus.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('menus', [
        'id' => $menu->id,
    ]);
});

test('管理者は予約があるメニューを削除できない', function () {
    $user = User::factory()->create();

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者',
        'role' => StaffRole::ADMIN,
    ]);

    $otherUser = User::factory()->create();

    $otherStaff = Staff::forceCreate([
        'user_id' => $otherUser->id,
        'name' => '担当スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    createReservation([
        'reservation_number' => 'RSV-20260917-OTHER',
        'cancellation_token' => 'hashed-token-other',
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
        'staff_id' => $otherStaff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-18 10:00:00',
        'end_at' => '2026-09-18 11:00:00',
        'status' => ReservationStatus::RESERVED,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('admin.menus.destroy', $menu));

    $response
        ->assertRedirect(route('admin.menus.index'))
        ->assertSessionHas('error', 'このメニューは予約に使用されているため削除できません。');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
    ]);
});
