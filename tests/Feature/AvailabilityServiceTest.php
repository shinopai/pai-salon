<?php

use App\Enums\ReservationStatus;
use App\Enums\StaffRole;
use App\Models\BusinessHour;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->staff = (new Staff())->forceFill([
        'user_id' => $this->user->id,
        'role' => StaffRole::STAFF,
        'name' => 'テストスタッフ',
    ]);
    $this->staff->save();

    $this->menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    $this->staff->menus()->attach($this->menu->id);

    $this->service = new AvailabilityService();
});

afterEach(function () {
    Carbon::setTestNow();
});

it('30分刻みで空き枠を算出する', function () {
    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $date = Carbon::create(2026, 9, 9, 0, 0, 0);
    Carbon::setTestNow(Carbon::create(2026, 9, 3, 10, 0, 0));

    $slots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
        $date,
    );

    expect($slots)->not->toBeEmpty()
        ->and($slots[0]['start_at']->format('H:i'))->toBe('10:00')
        ->and($slots[1]['start_at']->format('H:i'))->toBe('10:30')
        ->and($slots[2]['start_at']->format('H:i'))->toBe('11:00');
});

it('営業時間内の開始時刻だけを空き枠として返す', function () {
    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 9, 3, 10, 0, 0));

    $date = Carbon::create(2026, 9, 9);

    $slots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
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
});

it('durationを含めて営業時間内に収まる予約枠だけを返す', function () {
    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 9, 3, 10, 0, 0));

    $date = Carbon::create(2026, 9, 9);

    $slots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
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
});

it('予約可能期間内の日付だけを空き枠算出対象とする', function () {
    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 9, 4, 10, 0, 0));

    $withinPeriod = Carbon::create(2026, 11, 4);
    $outsidePeriod = Carbon::create(2026, 11, 5);
    $pastDate = Carbon::create(2026, 9, 3);

    $withinPeriodSlots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
        $withinPeriod,
    );

    $outsidePeriodSlots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
        $outsidePeriod,
    );

    $pastDateSlots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
        $pastDate,
    );

    expect($withinPeriodSlots)->not->toBeEmpty()
        ->and($outsidePeriodSlots)->toBeEmpty()
        ->and($pastDateSlots)->toBeEmpty();
});

it('当日は現在時刻から3時間以上先の予約枠だけを返す', function () {
    BusinessHour::create([
        'day_of_week' => 5,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 9, 4, 10, 0, 0));

    $date = Carbon::create(2026, 9, 4);

    $slots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
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
});

it('複数の条件を満たす空き枠だけを算出する', function () {
    BusinessHour::create([
        'day_of_week' => 3,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Carbon::setTestNow(Carbon::create(2026, 9, 3, 10, 0, 0));

    $date = Carbon::create(2026, 9, 9);

    createReservation([
        'reservation_number' => 'RSV-TEST-001',
        'customer_name' => '予約済み顧客',
        'customer_email' => 'reserved@example.com',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $date->copy()->setTime(11, 0),
        'end_at' => $date->copy()->setTime(12, 0),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-001',
    ]);

    createReservation([
        'reservation_number' => 'RSV-TEST-002',
        'customer_name' => 'キャンセル顧客',
        'customer_email' => 'cancelled@example.com',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $date->copy()->setTime(13, 0),
        'end_at' => $date->copy()->setTime(14, 0),
        'status' => ReservationStatus::CANCELLED,
        'cancellation_token' => 'hashed-token-002',
    ]);

    $slots = $this->service->getAvailableSlots(
        $this->menu->id,
        $this->staff->id,
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
});
