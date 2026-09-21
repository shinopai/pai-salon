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
