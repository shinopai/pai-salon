<?php

use App\Enums\ReservationStatus;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\User;
use App\Services\CancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Mail\CancellationCompletedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('CancellationServiceが存在する', function () {
    expect(class_exists(CancellationService::class))->toBeTrue();
});

it('予約をキャンセルできる', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TEST';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $service = new CancellationService();

    $service->cancel($reservation);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);
});

it('予約をキャンセルするとcancelled_atが設定される', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TEST';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $service = new CancellationService();

    $service->cancel($reservation);

    expect($reservation->fresh()->cancelled_at)
        ->not->toBeNull();
});

it('キャンセル期限である前日23時59分59秒まではキャンセルできる', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TEST';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo($reservation->start_at->copy()->subDay()->setTime(23, 59, 59));

    $service = new CancellationService();

    $service->cancel($reservation);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);
});

it('キャンセル期限を過ぎるとキャンセルできない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TEST';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->startOfDay()
    );

    $service = new CancellationService();

    expect(fn() => $service->cancel($reservation))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('すでにキャンセル済みの予約は再度キャンセルできない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TEST';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::CANCELLED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->cancelled_at = now()->subHour();
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    expect(fn() => $service->cancel($reservation))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('正しいキャンセルトークンで予約をキャンセルできる', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $rawToken = 'test-cancellation-token';

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TKN1';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make($rawToken);
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    $service->cancelByToken(
        $reservation->reservation_number,
        $rawToken,
    );

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);
});

it('不正なキャンセルトークンでは予約をキャンセルできない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TKN2';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('correct-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    expect(fn() => $service->cancelByToken(
        $reservation->reservation_number,
        'invalid-token',
    ))->toThrow(\Illuminate\Validation\ValidationException::class);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::RESERVED);
});

it('存在しない予約番号では予約をキャンセルできない', function () {
    $service = new CancellationService();

    expect(fn() => $service->cancelByToken(
        'RSV-20260909-NONE',
        'test-token',
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('完了済みの予約はキャンセルできない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-CMP1';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::COMPLETED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    expect(fn() => $service->cancelByToken(
        $reservation->reservation_number,
        'test-token',
    ))->toThrow(\Illuminate\Validation\ValidationException::class);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::COMPLETED);
});

it('キャンセル期限を過ぎた予約は正しいTokenでもキャンセルできない', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $rawToken = 'test-cancellation-token';

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-DL01';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make($rawToken);
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->startOfDay()
    );

    $service = new CancellationService();

    expect(fn() => $service->cancelByToken(
        $reservation->reservation_number,
        $rawToken,
    ))->toThrow(\Illuminate\Validation\ValidationException::class);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::RESERVED);

    expect($reservation->fresh()->cancelled_at)
        ->toBeNull();
});

it('キャンセル処理でstatusとcancelled_atが同一トランザクションで保存される', function () {
    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-TX01';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    $service->cancel($reservation);

    $reservation->refresh();

    expect($reservation->status)
        ->toBe(ReservationStatus::CANCELLED)
        ->and($reservation->cancelled_at)
        ->not->toBeNull();

    expect(
        Reservation::query()
            ->whereKey($reservation->id)
            ->where('status', ReservationStatus::CANCELLED)
            ->whereNotNull('cancelled_at')
            ->exists()
    )->toBeTrue();
});

it('キャンセルがCommitされた後にキャンセル完了メールが送信される', function () {
    Mail::fake();

    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-MAIL';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    $service->cancel($reservation);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);

    Mail::assertSent(
        CancellationCompletedMail::class,
        function (CancellationCompletedMail $mail) use ($reservation) {
            return $mail->reservation->is($reservation->fresh());
        }
    );
});
it('キャンセル処理がロールバックされた場合はメールを送信しない', function () {
    Mail::fake();

    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-ROLLBACK';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    Reservation::saving(function () {
        throw new RuntimeException('テスト用ロールバック');
    });

    $service = new CancellationService();

    expect(fn() => $service->cancel($reservation))
        ->toThrow(RuntimeException::class, 'テスト用ロールバック');

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::RESERVED);

    Mail::assertNothingSent();
});

it('キャンセル完了メールの送信に失敗してもキャンセル状態を維持しエラーをログに記録する', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andReturnSelf();

    Mail::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('メール送信失敗'));

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'キャンセル完了メールの送信に失敗しました。'
                && isset($context['reservation_number'])
                && isset($context['customer_email'])
                && isset($context['error'])
                && ! isset($context['cancellation_token']);
        });

    $user = User::create([
        'email' => 'staff@example.com',
        'password' => Hash::make('password'),
    ]);

    $staff = new Staff();
    $staff->user_id = $user->id;
    $staff->role = \App\Enums\StaffRole::STAFF;
    $staff->name = 'テストスタッフ';
    $staff->save();

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $customer = Customer::create([
        'name' => '既存顧客',
        'email' => 'existing@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->reservation_number = 'RSV-20260909-MAIL-FAIL';
    $reservation->customer_id = $customer->id;
    $reservation->customer_name = $customer->name;
    $reservation->customer_email = $customer->email;
    $reservation->staff_id = $staff->id;
    $reservation->menu_id = $menu->id;
    $reservation->start_at = now()->addDay()->setTime(10, 0);
    $reservation->end_at = now()->addDay()->setTime(11, 0);
    $reservation->status = ReservationStatus::RESERVED;
    $reservation->cancellation_token = Hash::make('test-token');
    $reservation->save();

    $this->travelTo(
        $reservation->start_at->copy()->subDay()->setTime(12, 0)
    );

    $service = new CancellationService();

    $service->cancel($reservation);

    expect($reservation->fresh()->status)
        ->toBe(ReservationStatus::CANCELLED);

    expect($reservation->fresh()->cancelled_at)
        ->not->toBeNull();
});
