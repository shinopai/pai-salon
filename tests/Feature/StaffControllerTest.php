<?php

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;
use App\Models\Menu;
use App\Models\StaffMenu;
use App\Enums\ReservationStatus;

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

test('スタッフは自身の情報を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => 'staff',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('staff.profile')
    );

    $response->assertOk();
    $response->assertViewIs('staff.profile');
    $response->assertViewHas('staff', $staff);
    $response->assertSee('テストスタッフ');
    $response->assertSee('staff@example.com');
    $response->assertSee('staff');
});

test('スタッフは自身の情報編集画面を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => 'staff',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('staff.profile.edit')
    );

    $response->assertOk();
    $response->assertViewIs('staff.profile-edit');
    $response->assertViewHas('staff', $staff);
    $response->assertSee('テストスタッフ');
    $response->assertSee('staff@example.com');
});

test('スタッフは自身の情報を更新してプロフィール画面へリダイレクトできる', function () {
    $user = User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '更新前スタッフ',
        'role' => 'staff',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('staff.profile.update'),
        [
            'name' => '更新後スタッフ',
        ]
    );

    $response->assertRedirect(
        route('staff.profile')
    );

    $this->assertDatabaseHas('staffs', [
        'id' => $staff->id,
        'name' => '更新後スタッフ',
    ]);
});

test('スタッフは自身が対応可能なメニューを表示できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => 'staff',
    ]);

    $availableMenu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $otherMenu = Menu::create([
        'name' => 'カラー',
        'duration' => 90,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $availableMenu->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('staff.menus.index')
    );

    $response->assertOk();
    $response->assertViewIs('staff.menus.index');
    $response->assertViewHas('menus', function ($menus) use ($availableMenu, $otherMenu) {
        return $menus->contains($availableMenu)
            && ! $menus->contains($otherMenu);
    });

    $response->assertSee('カット');
    $response->assertDontSee('カラー');
});

it('他スタッフの予約詳細にはアクセスできない', function () {
    $user = User::factory()->create();

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'ログインスタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

    $otherUser = User::factory()->create();

    $otherStaff = new Staff();

    $otherStaff->forceFill([
        'user_id' => $otherUser->id,
        'name' => '別スタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $reservation = createReservation([
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

    $this->actingAs($user)
        ->get("/staff/reservations/{$reservation->id}")
        ->assertForbidden();
});
