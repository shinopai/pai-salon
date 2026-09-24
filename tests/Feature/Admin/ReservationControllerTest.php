<?php

use App\Enums\ReservationStatus;
use App\Enums\StaffRole;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
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
