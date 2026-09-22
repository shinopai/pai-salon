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
