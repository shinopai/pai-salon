<?php

use App\Http\Requests\ReservationRequest;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function reservationRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new ReservationRequest();

    return Validator::make($data, $request->rules());
}

function validReservationData(): array
{
    $user = User::factory()->create();

    $staff = Staff::forceCreate([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => 'staff',
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    return [
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => '2026-09-10 10:00:00',
        'customer_name' => 'テスト太郎',
        'customer_email' => 'test@example.com',
    ];
}

test('有効な予約データはバリデーションを通過する', function () {
    $validator = reservationRequestValidator(validReservationData());

    expect($validator->passes())->toBeTrue();
});

test('staff_idは必須である', function () {
    $data = validReservationData();
    unset($data['staff_id']);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('存在しないstaff_idは許可されない', function () {
    $data = validReservationData();
    $data['staff_id'] = 99999;

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('menu_idは必須である', function () {
    $data = validReservationData();
    unset($data['menu_id']);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('存在しないmenu_idは許可されない', function () {
    $data = validReservationData();
    $data['menu_id'] = 99999;

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('start_atは必須である', function () {
    $data = validReservationData();
    unset($data['start_at']);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('start_at'))->toBeTrue();
});

test('不正なstart_atは許可されない', function () {
    $data = validReservationData();
    $data['start_at'] = 'invalid-date';

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('start_at'))->toBeTrue();
});

test('customer_nameは必須である', function () {
    $data = validReservationData();
    unset($data['customer_name']);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('customer_name'))->toBeTrue();
});

test('customer_nameは100文字まで許可される', function () {
    $data = validReservationData();
    $data['customer_name'] = str_repeat('あ', 100);

    $validator = reservationRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('101文字のcustomer_nameは許可されない', function () {
    $data = validReservationData();
    $data['customer_name'] = str_repeat('あ', 101);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('customer_name'))->toBeTrue();
});

test('customer_emailは必須である', function () {
    $data = validReservationData();
    unset($data['customer_email']);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});

test('不正なcustomer_emailは許可されない', function () {
    $data = validReservationData();
    $data['customer_email'] = 'invalid-email';

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});

test('256文字のcustomer_emailは許可されない', function () {
    $data = validReservationData();
    $data['customer_email'] = str_repeat('a', 256);

    $validator = reservationRequestValidator($data);

    expect($validator->errors()->has('customer_email'))->toBeTrue();
});
