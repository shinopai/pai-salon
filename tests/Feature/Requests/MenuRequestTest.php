<?php

use App\Http\Requests\MenuRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function menuRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = new MenuRequest();

    return Validator::make($data, $request->rules());
}

function validMenuData(): array
{
    return [
        'name' => 'テストメニュー',
        'duration' => 60,
    ];
}

test('有効なメニューデータはバリデーションを通過する', function () {
    $validator = menuRequestValidator(validMenuData());

    expect($validator->passes())->toBeTrue();
});

test('nameは必須である', function () {
    $data = validMenuData();
    unset($data['name']);

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは文字列である必要がある', function () {
    $data = validMenuData();
    $data['name'] = 12345;

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは100文字まで許可される', function () {
    $data = validMenuData();
    $data['name'] = str_repeat('あ', 100);

    $validator = menuRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('101文字のnameは許可されない', function () {
    $data = validMenuData();
    $data['name'] = str_repeat('あ', 101);

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('durationは必須である', function () {
    $data = validMenuData();
    unset($data['duration']);

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('duration'))->toBeTrue();
});

test('durationは整数である必要がある', function () {
    $data = validMenuData();
    $data['duration'] = '60.5';

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('duration'))->toBeTrue();
});

test('durationは1以上である必要がある', function () {
    $data = validMenuData();
    $data['duration'] = 0;

    $validator = menuRequestValidator($data);

    expect($validator->errors()->has('duration'))->toBeTrue();
});

test('durationが1の場合は許可される', function () {
    $data = validMenuData();
    $data['duration'] = 1;

    $validator = menuRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});
