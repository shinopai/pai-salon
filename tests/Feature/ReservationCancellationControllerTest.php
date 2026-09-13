<?php

use App\Enums\ReservationStatus;
use App\Enums\StaffRole;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('キャンセルURLへのGETで確認画面を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-TEST-001',
        'cancellation_token' => Hash::make('test-token'),
        'status' => ReservationStatus::RESERVED,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDays(3)->setTime(10, 0),
        'end_at' => now()->addDays(3)->setTime(11, 0),
    ]);

    $response = $this->get(
        route('reservations.cancel.show', [
            'reservation_number' => $reservation->reservation_number,
            'token' => 'test-token',
        ])
    );

    $response->assertStatus(200);
    $response->assertViewIs('reservations.cancel');
    $response->assertSee($reservation->reservation_number);
});

test('キャンセルURLへのGETだけでは予約はキャンセルされない', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-TEST-002',
        'cancellation_token' => Hash::make('test-token'),
        'status' => ReservationStatus::RESERVED,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer2@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDays(3)->setTime(10, 0),
        'end_at' => now()->addDays(3)->setTime(11, 0),
    ]);

    $this->get(
        route('reservations.cancel.show', [
            'reservation_number' => $reservation->reservation_number,
            'token' => 'test-token',
        ])
    );

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::RESERVED);
});

test('キャンセルURLへのPOSTで予約をキャンセルできる', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-TEST-003',
        'cancellation_token' => Hash::make('test-token'),
        'status' => ReservationStatus::RESERVED,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer3@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDays(3)->setTime(10, 0),
        'end_at' => now()->addDays(3)->setTime(11, 0),
    ]);

    $response = $this->post(
        route('reservations.cancel', [
            'reservation_number' => $reservation->reservation_number,
            'token' => 'test-token',
        ])
    );

    $response->assertStatus(200);
    $response->assertViewIs('reservations.cancel-complete');
    $response->assertSee($reservation->reservation_number);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);

    expect($reservation->fresh()->cancelled_at)
        ->not->toBeNull();
});

test('不正なトークンでは予約をキャンセルできない', function () {
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-TEST-004',
        'cancellation_token' => Hash::make('test-token'),
        'status' => ReservationStatus::RESERVED,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer4@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => now()->addDays(3)->setTime(10, 0),
        'end_at' => now()->addDays(3)->setTime(11, 0),
    ]);

    $response = $this->post(
        route('reservations.cancel', [
            'reservation_number' => $reservation->reservation_number,
            'token' => 'invalid-token',
        ])
    );

    $response->assertSessionHasErrors('reservation');

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::RESERVED);
});
