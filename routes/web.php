<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationCancellationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffReservationController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\StaffMenuController;
use App\Http\Controllers\Admin\BusinessHourController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        /**
         * 管理者
         */

        // スタッフ一覧
        Route::get('/staffs', [AdminStaffController::class, 'index'])
            ->name('staffs.index');

        // スタッフ登録画面
        Route::get('/staffs/create', [AdminStaffController::class, 'create'])
            ->name('staffs.create');

        // スタッフ登録
        Route::post('/staffs', [AdminStaffController::class, 'store'])
            ->name('staffs.store');

        // スタッフ詳細
        Route::get('/staffs/{staff}', [AdminStaffController::class, 'show'])
            ->name('staffs.show');

        // スタッフ編集
        Route::get('/staffs/{staff}/edit', [AdminStaffController::class, 'edit'])
            ->name('staffs.edit');

        // スタッフ更新
        Route::put('/staffs/{staff}', [AdminStaffController::class, 'update'])
            ->name('staffs.update');

        // スタッフ削除
        Route::delete('/staffs/{staff}', [AdminStaffController::class, 'destroy'])
            ->name('staffs.destroy');

        // 顧客一覧
        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        // 顧客詳細
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show');

        // 顧客編集
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit');

        // 顧客更新
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update');

        // メニュー一覧
        Route::get('/menus', [MenuController::class, 'index'])
            ->name('menus.index');

        // メニュー登録画面
        Route::get('/menus/create', [MenuController::class, 'create'])
            ->name('menus.create');

        // メニュー登録
        Route::post('/menus', [MenuController::class, 'store'])
            ->name('menus.store');

        // メニュー詳細
        Route::get('/menus/{menu}', [MenuController::class, 'show'])
            ->name('menus.show');

        // メニュー編集
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])
            ->name('menus.edit');

        // メニュー更新
        Route::put('/menus/{menu}', [MenuController::class, 'update'])
            ->name('menus.update');

        // メニュー削除
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])
            ->name('menus.destroy');

        // スタッフメニュー一覧
        Route::get('/staff-menus', [StaffMenuController::class, 'index'])
            ->name('staff-menus.index');

        // スタッフメニュー更新
        Route::put('/staff-menus', [StaffMenuController::class, 'update'])
            ->name('staff-menus.update');

        // 営業時間一覧
        Route::get('/business-hours', [BusinessHourController::class, 'index'])
            ->name('business-hours.index');
    });


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * スタッフ
     */

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
