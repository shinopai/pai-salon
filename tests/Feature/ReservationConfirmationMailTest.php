<?php

use App\Enums\ReservationStatus;
use App\Mail\ReservationConfirmationMail;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\StaffMenu;
use App\Models\User;
// use Illuminate\Support\Facades\Route;

// beforeEach(function () {
//     Route::get(
//         '/reservations/cancel/{reservation_number}/{token}',
//         fn() => null
//     )->name('reservations.cancel.show');
// });

function createReservationForMailTest(): array
{
    $user = User::factory()->create();

    $staff = new Staff();
    $staff->forceFill([
        'user_id' => $user->id,
        'name' => '佐藤',
        'role' => 'admin',
    ]);
    $staff->save();

    $menu = Menu::create([
        'name' => 'カット',
        'duration' => 60,
    ]);

    StaffMenu::create([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ]);

    $customer = Customer::create([
        'name' => '山田太郎',
        'email' => 'yamada@example.com',
    ]);

    $reservation = new Reservation();
    $reservation->forceFill([
        'reservation_number' => 'RSV-20260915-ABCD',
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-15 10:00:00',
        'end_at' => '2026-09-15 11:00:00',
        'status' => ReservationStatus::RESERVED,
        'cancellation_token' => '$2y$12$dummy',
    ]);
    $reservation->save();

    return [
        'reservation' => $reservation->fresh(),
        'rawToken' => 'test-cancellation-token',
    ];
}

it('予約確認メールの件名が正しい', function () {
    $data = createReservationForMailTest();

    $mail = new ReservationConfirmationMail(
        $data['reservation'],
        $data['rawToken'],
    );

    expect($mail->envelope()->subject)
        ->toBe('予約確認のお知らせ');
});

it('予約確認メールに予約番号が含まれる', function () {
    $data = createReservationForMailTest();

    $mail = new ReservationConfirmationMail(
        $data['reservation'],
        $data['rawToken'],
    );

    $html = $mail->render();

    expect($html)
        ->toContain($data['reservation']->reservation_number);
});

it('予約確認メールに予約日時が含まれる', function () {
    $data = createReservationForMailTest();

    $mail = new ReservationConfirmationMail(
        $data['reservation'],
        $data['rawToken'],
    );

    $html = $mail->render();

    expect($html)
        ->toContain('2026年09月15日 10:00')
        ->toContain('11:00');
});

it('予約確認メールにメニューと担当スタッフが含まれる', function () {
    $data = createReservationForMailTest();

    $mail = new ReservationConfirmationMail(
        $data['reservation'],
        $data['rawToken'],
    );

    $html = $mail->render();

    expect($html)
        ->toContain('カット')
        ->toContain('佐藤');
});

it('予約確認メールに顧客名が含まれる', function () {
    $data = createReservationForMailTest();

    $mail = new ReservationConfirmationMail(
        $data['reservation'],
        $data['rawToken'],
    );

    $html = $mail->render();

    expect($html)
        ->toContain('山田太郎');
});

it('予約確認メールに元のキャンセル用トークンを使用したURLが含まれる', function () {
    $data = createReservationForMailTest();

    $reservation = $data['reservation'];
    $rawToken = $data['rawToken'];

    $mail = new ReservationConfirmationMail(
        $reservation,
        $rawToken,
    );

    $html = $mail->render();

    $expectedUrl = route('reservations.cancel.show', [
        'reservation_number' => $reservation->reservation_number,
        'token' => $rawToken,
    ]);

    expect($html)
        ->toContain($expectedUrl);
});
