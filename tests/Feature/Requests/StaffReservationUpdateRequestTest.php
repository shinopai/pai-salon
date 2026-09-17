<?php

use App\Enums\ReservationStatus;
use App\Http\Requests\StaffReservationUpdateRequest;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function staffReservationUpdateRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new StaffReservationUpdateRequest();

    return Validator::make($data, $request->rules());
}

function validStaffReservationUpdateData(): array
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
        'start_at' => '2026-09-24 15:00:00',
        'status' => ReservationStatus::RESERVED,
    ];
}

test('有効な予約更新データはバリデーションを通過する', function () {
    $validator = staffReservationUpdateRequestValidator(
        validStaffReservationUpdateData()
    );

    expect($validator->passes())->toBeTrue();
});

test('staff_idは必須である', function () {
    $data = validStaffReservationUpdateData();

    unset($data['staff_id']);

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('存在しないstaff_idは許可されない', function () {
    $data = validStaffReservationUpdateData();

    $data['staff_id'] = 99999;

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('menu_idは必須である', function () {
    $data = validStaffReservationUpdateData();

    unset($data['menu_id']);

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('存在しないmenu_idは許可されない', function () {
    $data = validStaffReservationUpdateData();

    $data['menu_id'] = 99999;

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('start_atは必須である', function () {
    $data = validStaffReservationUpdateData();

    unset($data['start_at']);

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('start_at'))->toBeTrue();
});

test('不正なstart_atは許可されない', function () {
    $data = validStaffReservationUpdateData();

    $data['start_at'] = 'invalid-date';

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('start_at'))->toBeTrue();
});

test('statusは必須である', function () {
    $data = validStaffReservationUpdateData();

    unset($data['status']);

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('status'))->toBeTrue();
});

test('不正なstatusは許可されない', function () {
    $data = validStaffReservationUpdateData();

    $data['status'] = 'invalid-status';

    $validator = staffReservationUpdateRequestValidator($data);

    expect($validator->errors()->has('status'))->toBeTrue();
});
