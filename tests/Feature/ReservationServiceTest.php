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
use Illuminate\Validation\ValidationException;
use App\Mail\ReservationConfirmationMail;
use Illuminate\Support\Facades\Mail;

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

// テスト用の既存予約データ作成ヘルパー（NOT NULL制約とfillableを全考慮）
function createReservation(array $attributes): Reservation
{
    // customer_id が未指定の場合はダミーCustomerを自動作成してセット
    if (!isset($attributes['customer_id'])) {
        $customer = Customer::create([
            'name' => $attributes['customer_name'] ?? '既存顧客',
            'email' => $attributes['customer_email'] ?? 'existing@example.com',
        ]);
        $attributes['customer_id'] = $customer->id;
    }

    $reservation = new Reservation();
    $reservation->forceFill($attributes)->save();

    return $reservation;
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
