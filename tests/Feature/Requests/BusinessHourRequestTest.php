<?php

use App\Http\Requests\BusinessHourRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function businessHourRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    // リクエストデータ( $data )をセットして FormRequest インスタンスを生成する
    $request = BusinessHourRequest::create('/dummy', 'POST', $data);

    $validator = Validator::make($data, $request->rules());

    if (method_exists($request, 'withValidator')) {
        $request->withValidator($validator);
    }

    return $validator;
}

function validBusinessHourData(): array
{
    return [
        'day_of_week' => 1,
        'open_time' => '10:00',
        'close_time' => '20:00',
    ];
}

test('有効な営業時間データはバリデーションを通過する', function () {
    $validator = businessHourRequestValidator(validBusinessHourData());

    expect($validator->passes())->toBeTrue();
});

test('定休日としてopen_timeとclose_timeの両方がNULLの場合は許可される', function () {
    $data = validBusinessHourData();
    $data['open_time'] = null;
    $data['close_time'] = null;

    $validator = businessHourRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('day_of_weekは必須である', function () {
    $data = validBusinessHourData();
    unset($data['day_of_week']);

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('day_of_week'))->toBeTrue();
});

test('day_of_weekは整数である必要がある', function () {
    $data = validBusinessHourData();
    $data['day_of_week'] = 'monday';

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('day_of_week'))->toBeTrue();
});

test('day_of_weekは0から6まで許可される', function () {
    $data = validBusinessHourData();
    $data['day_of_week'] = 0;

    $validator = businessHourRequestValidator($data);

    expect($validator->passes())->toBeTrue();

    $data['day_of_week'] = 6;

    $validator = businessHourRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('7のday_of_weekは許可されない', function () {
    $data = validBusinessHourData();
    $data['day_of_week'] = 7;

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('day_of_week'))->toBeTrue();
});

test('open_timeはH:i形式である必要がある', function () {
    $data = validBusinessHourData();
    $data['open_time'] = '10:00:00';

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('open_time'))->toBeTrue();
});

test('close_timeはH:i形式である必要がある', function () {
    $data = validBusinessHourData();
    $data['close_time'] = '20:00:00';

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('close_time'))->toBeTrue();
});

test('open_timeのみ入力された場合は許可されない', function () {
    $data = validBusinessHourData();
    $data['close_time'] = null;

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('close_time'))->toBeTrue();
});

test('close_timeのみ入力された場合は許可されない', function () {
    $data = validBusinessHourData();
    $data['open_time'] = null;

    $validator = businessHourRequestValidator($data);

    expect($validator->errors()->has('open_time'))->toBeTrue();
});
