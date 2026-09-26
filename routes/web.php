<?php

use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationCancellationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffReservationController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\StaffMenuController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * 管理者
 */
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // スタッフ管理 (フルCRUD)
        Route::resource('staffs', AdminStaffController::class);

        // 顧客管理 (登録・削除なしのCRUD＆検索)
        Route::get('/customers/search', [CustomerController::class, 'search'])
            ->name('customers.search');
        Route::resource('customers', CustomerController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // メニュー管理 (フルCRUD)
        Route::resource('menus', MenuController::class);

        // スタッフメニュー設定 (個別エンドポイント)
        Route::get('/staff-menus', [StaffMenuController::class, 'index'])->name('staff-menus.index');
        Route::put('/staff-menus', [StaffMenuController::class, 'update'])->name('staff-menus.update');

        // 営業時間設定 (個別エンドポイント)
        Route::get('/business-hours', [BusinessHourController::class, 'index'])->name('business-hours.index');
        Route::put('/business-hours', [BusinessHourController::class, 'update'])->name('business-hours.update');

        // 休日管理(フルCRUD)
        Route::resource('holidays', HolidayController::class);

        // 予約管理 (編集・削除なしのCRUD)
        Route::resource('reservations', AdminReservationController::class)
            ->only(['index', 'show', 'update']);
    });

/**
 * スタッフ
 */
Route::middleware('auth')->group(function () {
    // ダッシュボード
    Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])
        ->name('staff.dashboard');

    // プロフィール
    Route::get('/staff/profile', [StaffController::class, 'profile'])
        ->name('staff.profile');

    // プロフィール編集
    Route::get('/staff/profile/edit', [StaffController::class, 'profileEdit'])
        ->name('staff.profile.edit');

    // プロフィール更新
    Route::put('/staff/profile', [StaffController::class, 'profileUpdate'])
        ->name('staff.profile.update');

    // 対応メニュー一覧
    Route::get('/staff/menus', [StaffController::class, 'menus'])
        ->name('staff.menus.index');

    // 予約一覧
    Route::get('/staff/reservations', [StaffReservationController::class, 'index'])
        ->name('staff.reservations.index');

    // 予約詳細
    Route::get('/staff/reservations/{reservation}', [StaffReservationController::class, 'show'])
        ->name('staff.reservations.show');

    // 予約更新
    Route::put('/staff/reservations/{reservation}', [StaffReservationController::class, 'update'])
        ->name('staff.reservations.update');
});

/**
 * 顧客予約
 */
Route::prefix('reservations')
    ->name('reservations.')
    ->group(function () {
        // メニュー選択
        Route::get('/menu', [ReservationController::class, 'menu'])
            ->name('menu');

        // スタッフ選択
        Route::get('/staff', [ReservationController::class, 'staff'])
            ->name('staff');

        // 日付選択
        Route::get('/date', [ReservationController::class, 'date'])
            ->name('date');

        // 空き枠表示
        Route::get('/slots', [ReservationController::class, 'slots'])
            ->name('slots');

        // 顧客情報入力
        Route::get('/customer', [ReservationController::class, 'customer'])
            ->name('customer');

        // 予約内容確認
        Route::get('/confirm', [ReservationController::class, 'confirm'])
            ->name('confirm');

        // 予約登録
        Route::post('/', [ReservationController::class, 'store'])
            ->name('store');

        // 予約完了
        Route::get('/complete', [ReservationController::class, 'complete'])
            ->name('complete');

        // 予約キャンセル
        Route::get(
            '/cancel/{reservation_number}/{token}',
            [ReservationCancellationController::class, 'show']
        )->name('cancel.show');

        Route::post(
            '/cancel/{reservation_number}/{token}',
            [ReservationCancellationController::class, 'cancel']
        )->name('cancel');
    });

require __DIR__ . '/auth.php';
