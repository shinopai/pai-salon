<?php

use App\Enums\StaffRole;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;

test('管理者はスタッフ・メニュー対応管理画面を表示できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    $response = $this->actingAs($user)
        ->get(route('admin.staff-menus.index'));

    $response
        ->assertOk()
        ->assertSee('スタッフ・メニュー対応管理')
        ->assertSee('佐藤')
        ->assertSee('カット');
});

test('管理者はスタッフに対応可能メニューを設定できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $response = $this->actingAs($user)
        ->put(route('admin.staff-menus.update'), [
            'staff_id' => $staff->id,
            'menu_ids' => [$menu->id],
        ]);

    $response
        ->assertRedirect(route('admin.staff-menus.index'));

    expect($staff->menus()->whereKey($menu->id)->exists())->toBeTrue();
});

test('管理者はスタッフの対応可能メニューを変更できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    $oldMenu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $newMenu = Menu::create([
        'name' => 'カラー',
        'duration' => 90,
    ]);

    $staff->menus()->attach($oldMenu->id);

    $response = $this->actingAs($user)
        ->put(route('admin.staff-menus.update'), [
            'staff_id' => $staff->id,
            'menu_ids' => [$newMenu->id],
        ]);

    $response
        ->assertRedirect(route('admin.staff-menus.index'));

    expect($staff->menus()->whereKey($oldMenu->id)->exists())->toBeFalse()
        ->and($staff->menus()->whereKey($newMenu->id)->exists())->toBeTrue();
});

test('管理者はスタッフの対応可能メニューを解除できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    $response = $this->actingAs($user)
        ->put(route('admin.staff-menus.update'), [
            'staff_id' => $staff->id,
            'menu_ids' => [],
        ]);

    $response
        ->assertRedirect(route('admin.staff-menus.index'));

    expect($staff->menus()->whereKey($menu->id)->exists())->toBeFalse();
});

test('同じスタッフとメニューの組み合わせは重複登録できない', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    expect(fn() => $staff->menus()->attach($menu->id))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
