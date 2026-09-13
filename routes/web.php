<?php

use App\Http\Controllers\ProfileController;
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
