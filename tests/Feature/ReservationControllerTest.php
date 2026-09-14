<?php

use App\Enums\StaffRole;
use App\Enums\ReservationStatus;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\StaffMenu;
use App\Models\User;
use App\Models\BusinessHour;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;

it('メニュー選択画面を表示できる', function () {
    Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    Menu::create([
        'name' => 'カラー',
        'duration' => 90,
    ]);

    $response = $this->get(route('reservations.menu'));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.menu');
    $response->assertViewHas('menus');

    $response->assertSee('カット');
    $response->assertSee('カラー');
});

it('選択したメニューに対応できるスタッフを表示できる', function () {
    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staffUser = User::factory()->create();

    $availableStaff = (new Staff())->forceFill([
        'user_id' => $staffUser->id,
        'role' => StaffRole::STAFF,
        'name' => '対応スタッフ',
    ]);
    $availableStaff->save();

    $unavailableStaffUser = User::factory()->create();

    $unavailableStaff = (new Staff())->forceFill([
        'user_id' => $unavailableStaffUser->id,
        'role' => StaffRole::STAFF,
        'name' => '非対応スタッフ',
    ]);
    $unavailableStaff->save();

    StaffMenu::create([
        'staff_id' => $availableStaff->id,
        'menu_id' => $menu->id,
    ]);

    $response = $this->get(route('reservations.staff', [
        'menu_id' => $menu->id,
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.staff');
    $response->assertViewHas('menu', $menu);
    $response->assertViewHas('staffs');

    $response->assertSee('対応スタッフ');
    $response->assertDontSee('非対応スタッフ');
});

it('選択したメニューとスタッフを指定して日付選択画面を表示できる', function () {
    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '担当スタッフ',
    ]);
    $staff->save();

    $response = $this->get(route('reservations.date', [
        'menu_id' => $menu->id,
        'staff_id' => $staff->id,
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.date');
    $response->assertViewHas('menu', $menu);
    $response->assertViewHas('staff', $staff);

    $response->assertSee('カット');
    $response->assertSee('担当スタッフ');
});

it('選択した予約情報を指定して顧客情報入力画面を表示できる', function () {
    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '担当スタッフ',
    ]);
    $staff->save();

    $date = '2026-09-20';
    $startAt = '2026-09-20 10:00:00';

    $response = $this->get(route('reservations.customer', [
        'menu_id' => $menu->id,
        'staff_id' => $staff->id,
        'date' => $date,
        'start_at' => $startAt,
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.customer');
    $response->assertViewHas('menu', $menu);
    $response->assertViewHas('staff', $staff);

    $response->assertViewHas('date', function ($value) use ($date) {
        return $value->format('Y-m-d') === $date;
    });

    $response->assertViewHas('startAt', function ($value) use ($startAt) {
        return $value->format('Y-m-d H:i:s') === $startAt;
    });

    $response->assertSee('カット');
    $response->assertSee('担当スタッフ');
    $response->assertSee('2026-09-20');
    $response->assertSee('10:00');
});

it('入力した顧客情報を指定して予約確認画面を表示できる', function () {
    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '担当スタッフ',
    ]);
    $staff->save();

    $date = '2026-09-20';
    $startAt = '2026-09-20 10:00:00';
    $customerName = 'テスト顧客';
    $customerEmail = 'customer@example.com';

    $response = $this->get(route('reservations.confirm', [
        'menu_id' => $menu->id,
        'staff_id' => $staff->id,
        'date' => $date,
        'start_at' => $startAt,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.confirm');
    $response->assertViewHas('menu', $menu);
    $response->assertViewHas('staff', $staff);

    $response->assertViewHas('date', function ($value) use ($date) {
        return $value->format('Y-m-d') === $date;
    });

    $response->assertViewHas('startAt', function ($value) use ($startAt) {
        return $value->format('Y-m-d H:i:s') === $startAt;
    });

    $response->assertViewHas('customerName', $customerName);
    $response->assertViewHas('customerEmail', $customerEmail);

    $response->assertSee('カット');
    $response->assertSee('担当スタッフ');
    $response->assertSee('2026-09-20');
    $response->assertSee('10:00');
    $response->assertSee('テスト顧客');
    $response->assertSee('customer@example.com');
});

it('予約を登録して予約完了画面へリダイレクトできる', function () {
    Mail::fake();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '担当スタッフ',
    ]);
    $staff->save();

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    BusinessHour::create([
        'day_of_week' => 4,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $startAt = '2026-09-24 15:00:00';

    $response = $this->post(route('reservations.store'), [
        'menu_id' => $menu->id,
        'staff_id' => $staff->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    $response->assertRedirect();

    $reservation = Reservation::query()
        ->where('customer_email', 'customer@example.com')
        ->firstOrFail();

    $response->assertRedirectToRoute('reservations.complete', [
        'reservation_number' => $reservation->reservation_number,
    ]);

    expect($reservation->status)->toBe(ReservationStatus::RESERVED);

    Mail::assertSent(\App\Mail\ReservationConfirmationMail::class);
});

it('予約完了画面に予約情報を表示できる', function () {
    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '担当スタッフ',
    ]);
    $staff->save();

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

    $response = $this->get(route('reservations.complete', [
        'reservation_number' => $reservation->reservation_number,
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('reservations.complete');
    $response->assertViewHas('reservation', $reservation);

    $response->assertSee('RSV-20260924-TEST');
    $response->assertSee('カット');
    $response->assertSee('担当スタッフ');
    $response->assertSee('2026-09-24 15:00');
    $response->assertSee('テスト顧客');
    $response->assertSee('customer@example.com');
});
