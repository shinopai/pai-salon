<?php

use App\Enums\ReservationStatus;
use App\Services\ReservationService;
use Carbon\Carbon;
use App\Enums\StaffRole;
use App\Models\StaffMenu;
use App\Models\User;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\BusinessHour;
use App\Models\Holiday;
use Illuminate\Validation\ValidationException;
use App\Mail\ReservationConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Staffの作成（forceFillでfillable回避）
    $this->staff = (new Staff())->forceFill([
        'user_id' => $this->user->id,
        'role' => StaffRole::STAFF,
        'name' => 'テストスタッフ',
    ]);
    $this->staff->save();

    $this->menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
    ]);

    $this->service = new ReservationService();
});

afterEach(function () {
    Carbon::setTestNow();
});

// Serviceに引き渡す入力値（$fillableに含まれる基本パラメータ）
function validPayload(array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ], $overrides);
}

it('スタッフが対応できないメニューでは予約できない', function () {
    $unsupportedMenu = Menu::create([
        'name' => '非対応メニュー',
        'duration' => 60,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $unsupportedMenu->id,
        'start_at' => $startAt,
    ])))->toThrow(ValidationException::class);
});

it('営業時間外の予約を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(19, 30);

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ])))->toThrow(ValidationException::class);
});

it('2か月を超える予約を拒否する', function () {
    $startAt = now()->addMonthsNoOverflow(2)->addDay()->setTime(10, 0);

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ])))->toThrow(ValidationException::class);
});

it('当日の3時間未満の予約を拒否する', function () {
    Carbon::setTestNow('2026-09-05 09:00:00');
    $startAt = Carbon::parse('2026-09-05 11:30:00');

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ])))->toThrow(ValidationException::class);
});

it('既存予約と重複する予約を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    createReservation([
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
        'reservation_number' => 'SVC-TEST-014',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-001',
    ]);

    $overlapStartAt = $startAt->copy()->addMinutes(30);

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $overlapStartAt,
    ])))->toThrow(ValidationException::class);
});

it('1分でも重複する予約は拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    createReservation([
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
        'reservation_number' => 'SVC-TEST-UT-011',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-ut-011',
    ]);

    $overlapStartAt = $startAt->copy()->addMinute();

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $overlapStartAt,
    ])))->toThrow(ValidationException::class);
});

it('隣接する予約は拒否しない', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    createReservation([
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
        'reservation_number' => 'SVC-TEST-UT-010',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-ut-010',
    ]);

    $adjacentStartAt = $startAt->copy()->addHour();

    BusinessHour::create([
        'day_of_week' => $adjacentStartAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $adjacentStartAt,
    ]));

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($this->staff->id)
        ->menu_id->toBe($this->menu->id);

    expect($reservation->start_at->equalTo($adjacentStartAt))->toBeTrue();
    expect($reservation->end_at->equalTo($adjacentStartAt->copy()->addHour()))->toBeTrue();

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
    ]);
});

it('キャンセル済み予約は重複判定の対象外とする', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    createReservation([
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
        'reservation_number' => 'SVC-TEST-CANCELLED',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::CANCELLED,
        'cancellation_token' => 'hashed-token-cancelled',
    ]);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    expect($reservation->status)->toBe(ReservationStatus::RESERVED);

    expect(
        Reservation::query()
            ->where('staff_id', $this->staff->id)
            ->where('start_at', $startAt)
            ->where('status', ReservationStatus::RESERVED)
            ->count()
    )->toBe(1);
});

it('予約登録失敗時にTransactionがrollbackされる', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $customerEmail = 'rollback@example.com';

    Reservation::creating(function () {
        throw new \RuntimeException('テスト用の予約登録失敗');
    });

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'customer_email' => $customerEmail,
    ])))->toThrow(\RuntimeException::class);

    expect(Customer::where('email', $customerEmail)->exists())->toBeFalse();
    expect(Reservation::where('customer_email', $customerEmail)->exists())->toBeFalse();
});

it('予約可能な時間帯で予約を登録できる', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($this->staff->id)
        ->menu_id->toBe($this->menu->id);

    expect($reservation->start_at->equalTo($startAt))->toBeTrue();
    expect($reservation->end_at->equalTo($startAt->copy()->addHour()))->toBeTrue();
});

it('予約時に既存顧客をメールアドレスで検索して利用する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
    ]));

    expect($reservation->customer_id)->toBe($customer->id);
    expect(Customer::where('email', 'existing@example.com')->count())->toBe(1);
});

it('予約時に存在しない顧客を新規作成する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $newEmail = 'new-customer@example.com';

    expect(Customer::where('email', $newEmail)->exists())->toBeFalse();

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'customer_name' => '新規顧客',
        'customer_email' => $newEmail,
    ]));

    $customer = Customer::where('email', $newEmail)->first();

    expect($customer)->not->toBeNull();
    expect($reservation->customer_id)->toBe($customer->id);
    expect($customer->name)->toBe('新規顧客');
});

it('予約登録時に予約番号を生成する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    expect($reservation->reservation_number)
        ->toMatch('/^RSV-\d{8}-[A-Z0-9]{4}$/')
        ->toStartWith('RSV-' . $startAt->format('Ymd') . '-');

    expect(Reservation::where('reservation_number', $reservation->reservation_number)->count())->toBe(1);
});

it('予約番号は予約ごとに一意に生成される', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation1 = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'customer_email' => 'customer1@example.com',
    ]));

    $reservation2 = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt->copy()->addHour(),
        'customer_email' => 'customer2@example.com',
    ]));

    expect($reservation1->reservation_number)->not->toBe($reservation2->reservation_number);
});

it('予約登録時にキャンセル用トークンをハッシュ化して保存する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    expect($reservation->cancellation_token)
        ->not->toBeNull()
        ->not->toBeEmpty();
})->skip('トークンのハッシュ化仕様確定まで保留');

it('予約登録が成功すると予約確認メールを送信する', function () {
    Mail::fake();

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    Mail::assertSent(
        ReservationConfirmationMail::class,
        function (ReservationConfirmationMail $mail) use ($reservation) {
            return $mail->reservation->is($reservation)
                && $mail->reservation->customer_email === $reservation->customer_email
                && $mail->cancellationToken !== '';
        }
    );
});

it('予約登録がロールバックされた場合は予約確認メールを送信しない', function () {
    Mail::fake();

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Reservation::creating(function () {
        throw new RuntimeException('予約登録失敗');
    });

    expect(fn() => $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ])))->toThrow(RuntimeException::class);

    Mail::assertNothingSent();

    expect(
        Reservation::query()
            ->where('staff_id', $this->staff->id)
            ->exists()
    )->toBeFalse();
});

it('予約確認メールの送信に失敗しても予約は登録されエラーをログに記録する', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andReturnSelf();

    Mail::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('メール送信失敗'));

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === '予約確認メールの送信に失敗しました。'
                && isset($context['reservation_number'])
                && isset($context['customer_email'])
                && isset($context['error'])
                && ! isset($context['cancellation_token']);
        });

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = $this->service->reserve(validPayload([
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
    ]));

    expect($reservation)->toBeInstanceOf(Reservation::class);

    expect(
        Reservation::query()
            ->whereKey($reservation->id)
            ->exists()
    )->toBeTrue();
});

it('予約を更新できる', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);
    $updatedStartAt = $startAt->copy()->addHours(2);

    BusinessHour::create([
        'day_of_week' => $updatedStartAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-001',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update',
    ]);

    $updatedReservation = $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $updatedStartAt,
        'status' => ReservationStatus::RESERVED,
    ]);

    expect($updatedReservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($this->staff->id)
        ->menu_id->toBe($this->menu->id);

    expect($updatedReservation->start_at->equalTo($updatedStartAt))
        ->toBeTrue();

    expect($updatedReservation->end_at->equalTo(
        $updatedStartAt->copy()->addHour()
    ))->toBeTrue();

    expect($updatedReservation->status)
        ->toBe(ReservationStatus::RESERVED);
});

it('予約更新時にメニュー変更に応じて終了時刻を再計算する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $newMenu = Menu::create([
        'name' => 'ロングメニュー',
        'duration' => 120,
    ]);

    StaffMenu::create([
        'staff_id' => $this->staff->id,
        'menu_id' => $newMenu->id,
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'menu-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-002',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-002',
    ]);

    $updatedReservation = $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $newMenu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]);

    expect($updatedReservation->menu_id)
        ->toBe($newMenu->id);

    expect($updatedReservation->start_at->equalTo($startAt))
        ->toBeTrue();

    expect($updatedReservation->end_at->equalTo(
        $startAt->copy()->addHours(2)
    ))->toBeTrue();
});

it('予約更新時に既存予約と重複する場合は拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => '更新対象顧客',
        'customer_email' => 'update-overlap@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-003',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-003',
    ]);

    $existingReservationStartAt = $startAt->copy()->addHours(2);

    createReservation([
        'customer_name' => '既存顧客',
        'customer_email' => 'existing-overlap@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-004',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $existingReservationStartAt,
        'end_at' => $existingReservationStartAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-004',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $existingReservationStartAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時は更新対象自身を重複判定の対象外とする', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'self-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-005',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-005',
    ]);

    $updatedReservation = $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]);

    expect($updatedReservation->id)
        ->toBe($reservation->id);

    expect($updatedReservation->start_at->equalTo($startAt))
        ->toBeTrue();

    expect($updatedReservation->end_at->equalTo(
        $startAt->copy()->addHour()
    ))->toBeTrue();

    expect($updatedReservation->status)
        ->toBe(ReservationStatus::RESERVED);
});

it('予約更新時にスタッフが対応していないメニューへの変更を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $unsupportedMenu = Menu::create([
        'name' => '対応外メニュー',
        'duration' => 60,
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'unsupported-menu@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-006',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-006',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $unsupportedMenu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時に休業日への変更を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    Holiday::create([
        'date' => $startAt->toDateString(),
        'reason' => '臨時休業',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'holiday-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-007',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-007',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時に営業時間外への変更を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);
    $outsideStartAt = $startAt->copy()->setTime(19, 30);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'outside-hours-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-008',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-008',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $outsideStartAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時に2か月を超える日付への変更を拒否する', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);
    $outsidePeriodStartAt = now()->addMonths(2)->addDay()->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $outsidePeriodStartAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'outside-period-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-009',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-009',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $outsidePeriodStartAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時に当日の3時間以内への変更を拒否する', function () {
    $now = Carbon::create(2026, 9, 16, 10, 0);
    Carbon::setTestNow($now);

    $startAt = $now->copy()->setTime(18, 0);
    $outsideLimitStartAt = $now->copy()->setTime(12, 59);

    BusinessHour::create([
        'day_of_week' => $now->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'same-day-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-010',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-010',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $outsideLimitStartAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('予約更新時にステータスをキャンセルへ変更できる', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'status-update@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-011',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-011',
    ]);

    $updatedReservation = $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::CANCELLED,
    ]);

    expect($updatedReservation->status)
        ->toBe(ReservationStatus::CANCELLED);
});

it('予約更新時に予約済みから完了へ変更できる', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'completed-status@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-012',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => 'hashed-token-update-012',
    ]);

    $updatedReservation = $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::COMPLETED,
    ]);

    expect($updatedReservation->status)
        ->toBe(ReservationStatus::COMPLETED);
});

it('キャンセル済み予約を予約済みに戻すことはできない', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'cancelled-restore@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-013',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::CANCELLED,
        'cancellation_token' => 'hashed-token-update-013',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('キャンセル済み予約を完了に変更することはできない', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'cancelled-completed@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-014',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::CANCELLED,
        'cancellation_token' => 'hashed-token-update-014',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::COMPLETED,
    ]))->toThrow(ValidationException::class);
});

it('完了済み予約を予約済みに戻すことはできない', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'completed-reserved@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-015',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::COMPLETED,
        'cancellation_token' => 'hashed-token-update-015',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::RESERVED,
    ]))->toThrow(ValidationException::class);
});

it('完了済み予約をキャンセルに変更することはできない', function () {
    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = createReservation([
        'customer_name' => 'テスト顧客',
        'customer_email' => 'completed-cancelled@example.com',
        'reservation_number' => 'SVC-TEST-UPDATE-016',
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'end_at' => $startAt->copy()->addHour(),
        'status' => ReservationStatus::COMPLETED,
        'cancellation_token' => 'hashed-token-update-016',
    ]);

    expect(fn() => $this->service->update($reservation, [
        'staff_id' => $this->staff->id,
        'menu_id' => $this->menu->id,
        'start_at' => $startAt,
        'status' => ReservationStatus::CANCELLED,
    ]))->toThrow(ValidationException::class);
});
