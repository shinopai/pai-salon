<?php

use App\Enums\StaffRole;
use App\Http\Requests\StaffRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function staffRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new StaffRequest();

    return Validator::make($data, $request->rules());
}

function validStaffData(): array
{
    return [
        'name' => 'テストスタッフ',
        'email' => 'staff@example.com',
        'role' => StaffRole::STAFF->value,
    ];
}

test('有効なスタッフデータはバリデーションを通過する', function () {
    $validator = staffRequestValidator(validStaffData());

    expect($validator->passes())->toBeTrue();
});

test('emailは必須である', function () {
    $data = validStaffData();

    unset($data['email']);

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('email形式でない値は許可されない', function () {
    $data = validStaffData();

    $data['email'] = 'invalid-email';

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('nameは必須である', function () {
    $data = validStaffData();

    unset($data['name']);

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは文字列である必要がある', function () {
    $data = validStaffData();

    $data['name'] = 12345;

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは100文字まで許可される', function () {
    $data = validStaffData();

    $data['name'] = str_repeat('あ', 100);

    $validator = staffRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('101文字のnameは許可されない', function () {
    $data = validStaffData();

    $data['name'] = str_repeat('あ', 101);

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('roleは必須である', function () {
    $data = validStaffData();

    unset($data['role']);

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('role'))->toBeTrue();
});

test('StaffRoleに定義されていないroleは許可されない', function () {
    $data = validStaffData();

    $data['role'] = 'invalid-role';

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('role'))->toBeTrue();
});

test('StaffRoleに定義されたroleは許可される', function () {
    $data = validStaffData();

    $data['role'] = StaffRole::ADMIN->value;

    $validator = staffRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('既に登録されているemailは許可されない', function () {
    User::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $data = validStaffData();

    $validator = staffRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});
