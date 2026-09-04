<?php

use App\Enums\StaffRole;
use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Customer;
use App\Enums\ReservationStatus;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('30分刻みで空き枠を算出する', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $service = new AvailabilityService();

    $date = Carbon::create(2026, 9, 9, 0, 0, 0);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 3, 10, 0, 0),
    );

    $slots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $date,
    );

    expect($slots)->not->toBeEmpty()
        ->and($slots[0]['start_at']->format('H:i'))->toBe('10:00')
        ->and($slots[1]['start_at']->format('H:i'))->toBe('10:30')
        ->and($slots[2]['start_at']->format('H:i'))->toBe('11:00');

    Carbon::setTestNow();
});

it('営業時間内の開始時刻だけを空き枠として返す', function () {
    $user = User::factory()->create([
        'email' => 'business-hours@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 3, 10, 0, 0),
    );

    $service = new AvailabilityService();

    $date = Carbon::create(2026, 9, 9);

    $slots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $date,
    );

    $startTimes = collect($slots)
        ->map(fn(array $slot) => $slot['start_at']->format('H:i'))
        ->all();

    expect($startTimes)
        ->toContain('10:00')
        ->toContain('19:00')
        ->not->toContain('09:30')
        ->not->toContain('20:00');

    Carbon::setTestNow();
});

it('durationを含めて営業時間内に収まる予約枠だけを返す', function () {
    $user = User::factory()->create([
        'email' => 'duration-business-hours@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 3, 10, 0, 0),
    );

    $service = new AvailabilityService();

    $date = Carbon::create(2026, 9, 9);

    $slots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $date,
    );

    $slot = collect($slots)
        ->firstWhere(
            fn(array $slot) => $slot['start_at']->format('H:i') === '19:00'
        );

    expect($slot)->not->toBeNull()
        ->and($slot['end_at']->format('H:i'))->toBe('20:00');

    expect(
        collect($slots)
            ->pluck('start_at')
            ->map(fn($startAt) => $startAt->format('H:i'))
            ->all()
    )->not->toContain('19:30');

    Carbon::setTestNow();
});

it('予約可能期間内の日付だけを空き枠算出対象とする', function () {
    $user = User::factory()->create([
        'email' => 'booking-period@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 4, 10, 0, 0),
    );

    $service = new AvailabilityService();

    $withinPeriod = Carbon::create(2026, 11, 4);
    $outsidePeriod = Carbon::create(2026, 11, 5);
    $pastDate = Carbon::create(2026, 9, 3);

    $withinPeriodSlots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $withinPeriod,
    );

    $outsidePeriodSlots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $outsidePeriod,
    );

    $pastDateSlots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $pastDate,
    );

    expect($withinPeriodSlots)->not->toBeEmpty()
        ->and($outsidePeriodSlots)->toBeEmpty()
        ->and($pastDateSlots)->toBeEmpty();

    Carbon::setTestNow();
});

it('当日は現在時刻から3時間以上先の予約枠だけを返す', function () {
    $user = User::factory()->create([
        'email' => 'same-day-limit@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 5,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 4, 10, 0, 0),
    );

    $service = new AvailabilityService();

    $date = Carbon::create(2026, 9, 4);

    $slots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $date,
    );

    $startTimes = collect($slots)
        ->pluck('start_at')
        ->map(fn($startAt) => $startAt->format('H:i'))
        ->all();

    expect($startTimes)
        ->not->toContain('12:30')
        ->toContain('13:00')
        ->toContain('13:30');

    Carbon::setTestNow();
});

it('複数の条件を満たす空き枠だけを算出する', function () {
    $user = User::factory()->create([
        'email' => 'availability-logic@example.com',
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $staff->menus()->attach($menu->id);

    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(
        Carbon::create(2026, 9, 3, 10, 0, 0),
    );

    $date = Carbon::create(2026, 9, 9);

    $customer = Customer::create([
        'name' => 'テスト顧客',
        'email' => 'customer@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-TEST-001';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = '予約済み顧客';
    $reservation->customer_email = 'reserved@example.com';
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = $date->copy()->setTime(11, 0);
    $reservation->end_at = $date->copy()->setTime(12, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = 'hashed-token-001';
    $reservation->save();

    $cancelledReservation = new Reservation();
    $cancelledReservation->reservation_number = 'RSV-TEST-002';
    $cancelledReservation->customer_id = $customer->id;
    $cancelledReservation->customer_name = 'キャンセル顧客';
    $cancelledReservation->customer_email = 'cancelled@example.com';
    $cancelledReservation->staff_id = $staff->id;
    $cancelledReservation->menu_id = $menu->id;
    $cancelledReservation->start_at = $date->copy()->setTime(13, 0);
    $cancelledReservation->end_at = $date->copy()->setTime(14, 0);
    $cancelledReservation->status = ReservationStatus::CANCELLED;
    $cancelledReservation->cancellation_token = 'hashed-token-002';
    $cancelledReservation->save();

    $service = new AvailabilityService();

    $slots = $service->getAvailableSlots(
        $menu->id,
        $staff->id,
        $date,
    );

    $startTimes = collect($slots)
        ->pluck('start_at')
        ->map(fn($startAt) => $startAt->format('H:i'))
        ->all();

    expect($startTimes)
        ->toContain('10:00')
        ->not->toContain('11:00')
        ->not->toContain('11:30')
        ->toContain('13:00')
        ->toContain('14:00')
        ->toContain('19:00')
        ->not->toContain('19:30');

    Carbon::setTestNow();
});
