<?php

use App\Enums\StaffRole;
use App\Enums\ReservationStatus;
use App\Models\Staff;
use App\Models\User;
use App\Models\Menu;
use App\Models\StaffMenu;
use App\Models\BusinessHour;

it('スタッフは予約一覧を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

    $this->actingAs($user)
        ->get('/staff/reservations')
        ->assertOk()
        ->assertViewIs('staff.reservations.index');
});

it('スタッフは自分が担当する予約だけ一覧で確認できる', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

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

    $myReservation = createReservation([
        'reservation_number' => 'RSV-20260920-TEST1',
        'cancellation_token' => 'hashed-token-1',
        'customer_name' => '自分の予約',
        'customer_email' => 'my@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-20 10:00:00',
        'end_at' => '2026-09-20 11:00:00',
        'status' => ReservationStatus::RESERVED,
    ]);

    $otherReservation = createReservation([
        'reservation_number' => 'RSV-20260920-TEST2',
        'cancellation_token' => 'hashed-token-2',
        'customer_name' => '別スタッフの予約',
        'customer_email' => 'other@example.com',
        'staff_id' => $otherStaff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-20 11:00:00',
        'end_at' => '2026-09-20 12:00:00',
        'status' => ReservationStatus::RESERVED,
    ]);

    $this->actingAs($user)
        ->get('/staff/reservations')
        ->assertOk()
        ->assertViewIs('staff.reservations.index')
        ->assertViewHas('reservations', function ($reservations) use ($myReservation, $otherReservation) {
            return $reservations->contains($myReservation)
                && ! $reservations->contains($otherReservation);
        });
});

it('スタッフは予約詳細を表示できる', function () {
    $user = User::factory()->create();

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ])->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-20260920-DETAIL',
        'cancellation_token' => 'hashed-token-detail',
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-20 10:00:00',
        'end_at' => '2026-09-20 11:00:00',
        'status' => ReservationStatus::RESERVED,
    ]);

    $this->actingAs($user)
        ->get("/staff/reservations/{$reservation->id}")
        ->assertOk()
        ->assertViewIs('staff.reservations.show')
        ->assertViewHas('reservation', $reservation);
});

test('スタッフは予約を更新して詳細画面へリダイレクトできる', function () {
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => 'staff',
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    BusinessHour::create([
        'day_of_week' => 4,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'reservation_number' => 'RSV-20260924-TEST',
        'cancellation_token' => 'hashed-token',
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-24 15:00:00',
        'end_at' => '2026-09-24 16:00:00',
        'status' => ReservationStatus::RESERVED,
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('staff.reservations.update', $reservation),
        [
            'staff_id' => $staff->id,
            'menu_id' => $menu->id,
            'start_at' => '2026-09-24 16:00:00',
            'status' => ReservationStatus::RESERVED->value,
        ]
    );

    $response->assertRedirect(
        route('staff.reservations.show', $reservation)
    );

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'start_at' => '2026-09-24 16:00:00',
        'end_at' => '2026-09-24 17:00:00',
        'status' => ReservationStatus::RESERVED->value,
    ]);
});
