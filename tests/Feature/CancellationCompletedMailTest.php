<?php

use App\Enums\ReservationStatus;
use App\Mail\CancellationCompletedMail;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\StaffMenu;
use App\Models\User;

function createReservationForCancellationMailTest(): Reservation
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
        'status' => ReservationStatus::CANCELLED,
        'cancellation_token' => '$2y$12$dummy',
        'cancelled_at' => '2026-09-14 12:00:00',
    ]);
    $reservation->save();

    return $reservation->fresh();
}

it('キャンセル完了メールの件名が正しい', function () {
    $reservation = createReservationForCancellationMailTest();

    $mail = new CancellationCompletedMail($reservation);

    expect($mail->envelope()->subject)
        ->toBe('キャンセル完了のお知らせ');
});

it('キャンセル完了メールに予約番号が含まれる', function () {
    $reservation = createReservationForCancellationMailTest();

    $mail = new CancellationCompletedMail($reservation);

    $html = $mail->render();

    expect($html)
        ->toContain($reservation->reservation_number);
});

it('キャンセル完了メールに予約日時が含まれる', function () {
    $reservation = createReservationForCancellationMailTest();

    $mail = new CancellationCompletedMail($reservation);

    $html = $mail->render();

    expect($html)
        ->toContain('2026年09月15日 10:00');
});

it('キャンセル完了メールにメニューと担当スタッフが含まれる', function () {
    $reservation = createReservationForCancellationMailTest();

    $mail = new CancellationCompletedMail($reservation);

    $html = $mail->render();

    expect($html)
        ->toContain('カット')
        ->toContain('佐藤');
});

it('キャンセル完了メールにキャンセル完了の旨が含まれる', function () {
    $reservation = createReservationForCancellationMailTest();

    $mail = new CancellationCompletedMail($reservation);

    $html = $mail->render();

    expect($html)
        ->toContain('ご予約のキャンセルが完了しました。');
});
