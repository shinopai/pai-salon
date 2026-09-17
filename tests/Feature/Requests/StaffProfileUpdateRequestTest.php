<?php

use App\Http\Requests\StaffProfileUpdateRequest;
use Illuminate\Support\Facades\Validator;

function staffProfileUpdateRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new StaffProfileUpdateRequest();

    return Validator::make($data, $request->rules());
}

test('有効なスタッフプロフィール更新データはバリデーションを通過する', function () {
    $validator = staffProfileUpdateRequestValidator([
        'name' => '更新後スタッフ',
    ]);

    expect($validator->passes())->toBeTrue();
});

test('nameは必須である', function () {
    $validator = staffProfileUpdateRequestValidator([]);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは文字列でなければならない', function () {
    $validator = staffProfileUpdateRequestValidator([
        'name' => 12345,
    ]);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは255文字以内である', function () {
    $validator = staffProfileUpdateRequestValidator([
        'name' => str_repeat('あ', 256),
    ]);

    expect($validator->errors()->has('name'))->toBeTrue();
});
