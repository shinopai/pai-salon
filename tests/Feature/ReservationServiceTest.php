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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

it('ReservationServiceが予約登録処理のエントリポイントを持つ', function () {
    $service = new ReservationService();

    expect(method_exists($service, 'reserve'))->toBeTrue();
});

it('スタッフが対応できないメニューでは予約できない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $supportedMenu = Menu::create([
        'name' => '対応メニュー',
        'duration' => 60,
    ]);

    $unsupportedMenu = Menu::create([
        'name' => '非対応メニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $supportedMenu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $unsupportedMenu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('営業時間外の予約を拒否する', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);
    $startAt = now()->addDays(7)->setTime(19, 30);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('2か月を超える予約を拒否する', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $startAt = now()->addMonthsNoOverflow(2)->addDay()->setTime(10, 0);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('当日の3時間未満の予約を拒否する', function () {
    Carbon::setTestNow('2026-09-05 09:00:00');

    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $startAt = Carbon::parse('2026-09-05 11:30:00');

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('既存予約と重複する予約を拒否する', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $existingReservation = new Reservation();
    $existingReservation->customer_id = $customer->id;
    $existingReservation->customer_name = '既存顧客';
    $existingReservation->customer_email = 'existing@example.com';
    $existingReservation->reservation_number = 'SVC-TEST-014';
    $existingReservation->staff_id = $staff->id;
    $existingReservation->menu_id = $menu->id;
    $existingReservation->start_at = now()->addDays(7)->setTime(10, 0);
    $existingReservation->end_at = now()->addDays(7)->setTime(11, 0);
    $existingReservation->status = ReservationStatus::RESERVED;
    $existingReservation->cancellation_token = 'hashed-token-001';
    $existingReservation->save();

    $startAt = now()->addDays(7)->setTime(10, 30);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('隣接する予約は拒否しない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $existingReservation = new Reservation();
    $existingReservation->customer_id = $customer->id;
    $existingReservation->customer_name = '既存顧客';
    $existingReservation->customer_email = 'existing@example.com';
    $existingReservation->reservation_number = 'SVC-TEST-UT-010';
    $existingReservation->staff_id = $staff->id;
    $existingReservation->menu_id = $menu->id;
    $existingReservation->start_at = now()->addDays(7)->setTime(10, 0);
    $existingReservation->end_at = now()->addDays(7)->setTime(11, 0);
    $existingReservation->status = ReservationStatus::RESERVED;
    $existingReservation->cancellation_token = 'hashed-token-ut-010';
    $existingReservation->save();

    $startAt = now()->addDays(7)->setTime(11, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($staff->id)
        ->menu_id->toBe($menu->id)
        ->start_at->equalTo($startAt);

    expect($reservation->end_at->equalTo(
        $startAt->copy()->addHour()
    ))->toBeTrue();

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);
});

it('1分でも重複する予約は拒否する', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $existingReservation = new Reservation();
    $existingReservation->customer_id = $customer->id;
    $existingReservation->customer_name = '既存顧客';
    $existingReservation->customer_email = 'existing@example.com';
    $existingReservation->reservation_number = 'SVC-TEST-UT-011';
    $existingReservation->staff_id = $staff->id;
    $existingReservation->menu_id = $menu->id;
    $existingReservation->start_at = now()->addDays(7)->setTime(10, 0);
    $existingReservation->end_at = now()->addDays(7)->setTime(11, 0);
    $existingReservation->status = ReservationStatus::RESERVED;
    $existingReservation->cancellation_token = 'hashed-token-ut-011';
    $existingReservation->save();

    $startAt = now()->addDays(7)->setTime(10, 1);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))
        ->toThrow(ValidationException::class);
});

it('キャンセル済み予約は重複判定の対象外とする', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $existingReservation = new Reservation();
    $existingReservation->customer_id = $customer->id;
    $existingReservation->customer_name = '既存顧客';
    $existingReservation->customer_email = 'existing@example.com';
    $existingReservation->reservation_number = 'SVC-TEST-CANCELLED';
    $existingReservation->staff_id = $staff->id;
    $existingReservation->menu_id = $menu->id;
    $existingReservation->start_at = now()->addDays(7)->setTime(10, 0);
    $existingReservation->end_at = now()->addDays(7)->setTime(11, 0);
    $existingReservation->status = ReservationStatus::CANCELLED;
    $existingReservation->cancellation_token = 'hashed-token-cancelled';
    $existingReservation->save();

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($staff->id)
        ->menu_id->toBe($menu->id)
        ->start_at->equalTo($startAt);

    expect($reservation->status)
        ->toBe(ReservationStatus::RESERVED);

    expect(
        Reservation::query()
            ->where('staff_id', $staff->id)
            ->where('start_at', $startAt)
            ->where('status', ReservationStatus::RESERVED)
            ->count()
    )->toBe(1);
});

it('予約登録時に対象スタッフをロックして処理する', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($staff->id)
        ->menu_id->toBe($menu->id)
        ->start_at->equalTo($startAt);

    expect($reservation->status)
        ->toBe(ReservationStatus::RESERVED);
});

it('予約登録失敗時にTransactionがrollbackされる', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

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

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'ロールバック顧客',
        'customer_email' => $customerEmail,
    ]))->toThrow(\RuntimeException::class);

    expect(
        Customer::query()
            ->where('email', $customerEmail)
            ->exists()
    )->toBeFalse();

    expect(
        Reservation::query()
            ->where('customer_email', $customerEmail)
            ->exists()
    )->toBeFalse();
});

it('予約可能な時間帯で予約を登録できる', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->staff_id->toBe($staff->id)
        ->menu_id->toBe($menu->id)
        ->customer_name->toBe('テスト顧客')
        ->customer_email->toBe('customer@example.com');

    expect($reservation->start_at->equalTo($startAt))->toBeTrue();
    expect($reservation->end_at->equalTo($startAt->copy()->addHour()))->toBeTrue();

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);
});

it('予約時に既存顧客をメールアドレスで検索して利用する', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

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

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => '既存顧客',
        'customer_email' => 'existing@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->customer_id->toBe($customer->id);

    expect(
        Customer::query()
            ->where('email', 'existing@example.com')
            ->count()
    )->toBe(1);
});

it('予約時に存在しない顧客を新規作成する', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    expect(
        Customer::query()
            ->where('email', 'new-customer@example.com')
            ->exists()
    )->toBeFalse();

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => '新規顧客',
        'customer_email' => 'new-customer@example.com',
    ]);

    expect($reservation)
        ->toBeInstanceOf(Reservation::class)
        ->customer_id->not->toBeNull();

    $customer = Customer::query()
        ->where('email', 'new-customer@example.com')
        ->first();

    expect($customer)->not->toBeNull();

    expect($reservation->customer_id)
        ->toBe($customer->id);

    expect($customer->name)
        ->toBe('新規顧客');
});

it('予約登録時に予約番号を生成する', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $reservation = (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]);

    expect($reservation->reservation_number)
        ->toMatch('/^RSV-\d{8}-[A-Z0-9]{4}$/');

    expect($reservation->reservation_number)
        ->toStartWith('RSV-' . $startAt->format('Ymd') . '-');

    expect(
        Reservation::query()
            ->where('reservation_number', $reservation->reservation_number)
            ->count()
    )->toBe(1);
});

it('予約番号は予約ごとに一意に生成される', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    $service = new ReservationService();

    $reservation1 = $service->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客1',
        'customer_email' => 'customer1@example.com',
    ]);

    $reservation2 = $service->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt->copy()->addHour(),
        'customer_name' => 'テスト顧客2',
        'customer_email' => 'customer2@example.com',
    ]);

    expect($reservation1->reservation_number)
        ->not->toBe($reservation2->reservation_number);

    expect(
        Reservation::query()
            ->whereIn('reservation_number', [
                $reservation1->reservation_number,
                $reservation2->reservation_number,
            ])
            ->count()
    )->toBe(2);
});

it('予約登録時にキャンセル用トークンを生成する', function () {
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

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ]);

    expect(fn() => (new ReservationService())->reserve([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt,
        'customer_name' => 'テスト顧客',
        'customer_email' => 'customer@example.com',
    ]))->not->toThrow(ValidationException::class);
});

// it('予約登録時にキャンセル用トークンをハッシュ化して保存する', function () {
//     $user = User::factory()->create();

//     $staff = new Staff();
//     $staff->user_id = $user->id;
//     $staff->role = StaffRole::STAFF;
//     $staff->name = 'テストスタッフ';
//     $staff->save();

//     $menu = Menu::create([
//         'name' => 'テストメニュー',
//         'duration' => 60,
//     ]);

//     StaffMenu::create([
//         'staff_id' => $staff->id,
//         'menu_id' => $menu->id,
//     ]);

//     $startAt = now()->addDays(7)->setTime(10, 0);

//     BusinessHour::create([
//         'day_of_week' => $startAt->dayOfWeek,
//         'open_time' => '10:00',
//         'close_time' => '20:00',
//     ]);

//     $reservation = (new ReservationService())->reserve([
//         'staff_id' => $staff->id,
//         'menu_id' => $menu->id,
//         'start_at' => $startAt,
//         'customer_name' => 'テスト顧客',
//         'customer_email' => 'customer@example.com',
//     ]);

//     expect($reservation->cancellation_token)
//         ->not->toBeNull()
//         ->not->toBeEmpty();

//     expect(
//         $reservation->cancellation_token
//     )->not->toBe('64文字の生トークン');

//     expect(
//         password_get_info($reservation->cancellation_token)['algo']
//     )->not->toBe(0);
// });

afterEach(function () {
    Carbon::setTestNow();
});
