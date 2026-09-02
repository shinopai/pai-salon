<?php

use App\Http\Requests\StaffMenuRequest;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\User;
use App\Enums\StaffRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function staffMenuRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = StaffMenuRequest::create('/dummy', 'POST', $data);

    return Validator::make(
        $data,
        $request->rules()
    );
}

function validStaffMenuData(): array
{
    $user = User::create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = Staff::create([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    return [
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ];
}

test('有効なスタッフとメニューの組み合わせはバリデーションを通過する', function () {
    $validator = staffMenuRequestValidator(validStaffMenuData());

    expect($validator->passes())->toBeTrue();
});

test('staff_idは必須である', function () {
    $data = validStaffMenuData();
    unset($data['staff_id']);

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('staff_idは整数である必要がある', function () {
    $data = validStaffMenuData();
    $data['staff_id'] = 'staff';

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('staff_idは存在するスタッフである必要がある', function () {
    $data = validStaffMenuData();
    $data['staff_id'] = 999999;

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('staff_id'))->toBeTrue();
});

test('menu_idは必須である', function () {
    $data = validStaffMenuData();
    unset($data['menu_id']);

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('menu_idは整数である必要がある', function () {
    $data = validStaffMenuData();
    $data['menu_id'] = 'menu';

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('menu_idは存在するメニューである必要がある', function () {
    $data = validStaffMenuData();
    $data['menu_id'] = 999999;

    $validator = staffMenuRequestValidator($data);

    expect($validator->errors()->has('menu_id'))->toBeTrue();
});

test('staff_idとmenu_idが両方有効ならバリデーションを通過する', function () {
    $user = User::create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = Staff::create([
        'user_id' => $user->id,
        'name' => 'テストスタッフ',
        'role' => StaffRole::STAFF,
    ]);

    $menu = Menu::create([
        'name' => 'テストメニュー',
        'duration' => 60,
    ]);

    $data = [
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
    ];

    $validator = staffMenuRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});
