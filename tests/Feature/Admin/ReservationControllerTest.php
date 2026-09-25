<?php

use App\Enums\ReservationStatus;
use App\Enums\StaffRole;
use App\Models\BusinessHour;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\StaffMenu;
use App\Models\User;

test('管理者が全予約一覧を表示できる', function () {
    $user = User::create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $otherUser = User::create([
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
    ]);

    $otherStaff = new Staff();
    $otherStaff->forceFill([
        'user_id' => $otherUser->id,
        'name' => '一般スタッフ',
        'role' => StaffRole::STAFF,
    ]);
    $otherStaff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => 'テスト顧客',
        'email' => 'customer@example.com',
    ]);

    $firstReservation = new Reservation();
    $firstReservation->forceFill([
        'reservation_number' => 'RSV-20260924-0001',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDay()->setTime(10, 0),
        'end_at' => now()->addDay()->setTime(11, 0),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => bcrypt('token-1'),
    ]);
    $firstReservation->save();

    $secondReservation = new Reservation();
    $secondReservation->forceFill([
        'reservation_number' => 'RSV-20260924-0002',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $otherStaff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDay()->setTime(11, 0),
        'end_at' => now()->addDay()->setTime(12, 0),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => bcrypt('token-2'),
    ]);
    $secondReservation->save();

    $this->actingAs($user)
        ->get(route('admin.reservations.index'))
        ->assertOk()
        ->assertSee($firstReservation->reservation_number)
        ->assertSee($secondReservation->reservation_number);
});

test('管理者が予約詳細を表示できる', function () {
    $user = User::create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => 'テスト顧客',
        'email' => 'customer@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->forceFill([
        'reservation_number' => 'RSV-20260924-0003',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDay()->setTime(10, 0),
        'end_at' => now()->addDay()->setTime(11, 0),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => bcrypt('token-3'),
    ]);
    $reservation->save();

    $this->actingAs($user)
        ->get(route('admin.reservations.show', $reservation))
        ->assertOk()
        ->assertSee($reservation->reservation_number)
        ->assertSee($reservation->customer_name)
        ->assertSee($reservation->customer_email)
        ->assertSee($staff->name)
        ->assertSee($menu->name)
        ->assertSee($reservation->start_at->format('Y-m-d H:i:s'))
        ->assertSee($reservation->end_at->format('Y-m-d H:i:s'))
        ->assertSee($reservation->status->value);
});

test('管理者が予約を更新できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $updatedUser = User::factory()->create();

    $updatedStaff = Staff::forceCreate([
        'user_id' => $updatedUser->id,
        'name' => '変更後スタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $updatedMenu = Menu::create([
        'name' => 'カラー',
        'duration' => 90,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    StaffMenu::create([
        'staff_id' => $updatedStaff->id,
        'menu_id' => $updatedMenu->id,
    ]);

    $startAt = now()->addDay()->setTime(15, 0, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $customer = Customer::create([
        'name' => 'テスト顧客',
        'email' => 'admin-reservation-update@example.com',
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-20260925-ADMIN',
        'cancellation_token' => 'hashed-token',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt->format('Y-m-d H:i:s'),
        'end_at' => $startAt->copy()->addMinutes(60)->format('Y-m-d H:i:s'),
        'status' => ReservationStatus::RESERVED,
    ]);

    $updatedStartAt = $startAt->copy()->addHour();

    $this->actingAs($user);

    $response = $this->put(
        route('admin.reservations.update', $reservation),
        [
            'staff_id' => $updatedStaff->id,
            'menu_id' => $updatedMenu->id,
            'start_at' => $updatedStartAt->format('Y-m-d H:i:s'),
            'status' => ReservationStatus::COMPLETED->value,
        ]
    );

    $response->assertRedirect(
        route('admin.reservations.show', $reservation)
    );

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'staff_id' => $updatedStaff->id,
        'menu_id' => $updatedMenu->id,
        'start_at' => $updatedStartAt->format('Y-m-d H:i:s'),
        'end_at' => $updatedStartAt->copy()->addMinutes(90)->format('Y-m-d H:i:s'),
        'status' => ReservationStatus::COMPLETED->value,
    ]);
});

test('管理者が自身の担当予約を操作できる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDay()->setTime(15, 0, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $customer = Customer::create([
        'name' => '担当予約テスト顧客',
        'email' => 'admin-own-reservation@example.com',
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-20260925-OWN',
        'cancellation_token' => 'hashed-own-token',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt->format('Y-m-d H:i:s'),
        'end_at' => $startAt->copy()->addMinutes(60)->format('Y-m-d H:i:s'),
        'status' => ReservationStatus::RESERVED,
    ]);

    $updatedStartAt = $startAt->copy()->addHour();

    $this->actingAs($user);

    $response = $this->put(
        route('admin.reservations.update', $reservation),
        [
            'staff_id' => $staff->id,
            'menu_id' => $menu->id,
            'start_at' => $updatedStartAt->format('Y-m-d H:i:s'),
            'status' => ReservationStatus::COMPLETED->value,
        ]
    );

    $response->assertRedirect(
        route('admin.reservations.show', $reservation)
    );

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $updatedStartAt->format('Y-m-d H:i:s'),
        'end_at' => $updatedStartAt->copy()->addMinutes(60)->format('Y-m-d H:i:s'),
        'status' => ReservationStatus::COMPLETED->value,
    ]);
});
