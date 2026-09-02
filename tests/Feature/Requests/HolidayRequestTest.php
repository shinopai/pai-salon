<?php

use App\Http\Requests\HolidayRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function holidayRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = HolidayRequest::create('/dummy', 'POST', $data);
    $validator = Validator::make($data, $request->rules());

    return $validator;
}

function validHolidayData(): array
{
    return [
        'date' => '2026-12-31',
        'reason' => '年末年始休業',
    ];
}

test('有効な休日データはバリデーションを通過する', function () {
    $validator = holidayRequestValidator(validHolidayData());

    expect($validator->passes())->toBeTrue();
});

test('dateは必須である', function () {
    $data = validHolidayData();
    unset($data['date']);

    $validator = holidayRequestValidator($data);

    expect($validator->errors()->has('date'))->toBeTrue();
});

test('dateはY-m-d形式である必要がある', function () {
    $data = validHolidayData();
    $data['date'] = '2026/12/31';

    $validator = holidayRequestValidator($data);

    expect($validator->errors()->has('date'))->toBeTrue();
});

test('reasonは必須である', function () {
    $data = validHolidayData();
    unset($data['reason']);

    $validator = holidayRequestValidator($data);

    expect($validator->errors()->has('reason'))->toBeTrue();
});

test('reasonは文字列である必要がある', function () {
    $data = validHolidayData();
    $data['reason'] = 123;

    $validator = holidayRequestValidator($data);

    expect($validator->errors()->has('reason'))->toBeTrue();
});

test('reasonは255文字まで許可される', function () {
    $data = validHolidayData();
    $data['reason'] = str_repeat('あ', 255);

    $validator = holidayRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('reasonが256文字の場合は許可されない', function () {
    $data = validHolidayData();
    $data['reason'] = str_repeat('あ', 256);

    $validator = holidayRequestValidator($data);

    expect($validator->errors()->has('reason'))->toBeTrue();
});
