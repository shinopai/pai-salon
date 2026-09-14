<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationCancellationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * 予約
 */

// メニュー選択
Route::get(
    '/reservations/menu',
    [ReservationController::class, 'menu']
)->name('reservations.menu');

// スタッフ選択
Route::get(
    '/reservations/staff',
    [ReservationController::class, 'staff']
)->name('reservations.staff');

// 日付選択
Route::get(
    '/reservations/date',
    [ReservationController::class, 'date']
)->name('reservations.date');

// 空き枠表示
Route::get(
    '/reservations/slots',
    [ReservationController::class, 'slots']
)->name('reservations.slots');

// 顧客情報入力
Route::get(
    '/reservations/customer',
    [ReservationController::class, 'customer']
)->name('reservations.customer');

// 予約内容確認
Route::get(
    '/reservations/confirm',
    [ReservationController::class, 'confirm']
)->name('reservations.confirm');

// 予約登録
Route::post(
    '/reservations',
    [ReservationController::class, 'store']
)->name('reservations.store');

// 予約完了
Route::get(
    '/reservations/complete',
    [ReservationController::class, 'complete']
)->name('reservations.complete');

// 予約キャンセル
Route::get(
    '/reservations/cancel/{reservation_number}/{token}',
    [ReservationCancellationController::class, 'show']
)->name('reservations.cancel.show');

Route::post(
    '/reservations/cancel/{reservation_number}/{token}',
    [ReservationCancellationController::class, 'cancel']
)->name('reservations.cancel');

require __DIR__ . '/auth.php';
