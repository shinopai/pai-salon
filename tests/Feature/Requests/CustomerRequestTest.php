<?php

use App\Http\Requests\CustomerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function customerRequestValidator(array $data): \Illuminate\Contracts\Validation\Validator
{
    $request = CustomerRequest::create('/dummy', 'POST', $data);

    return Validator::make(
        $data,
        $request->rules()
    );
}

function validCustomerData(): array
{
    return [
        'name' => '山田太郎',
        'email' => 'yamada@example.com',
    ];
}

test('有効な顧客データはバリデーションを通過する', function () {
    $validator = customerRequestValidator(validCustomerData());

    expect($validator->passes())->toBeTrue();
});

test('nameは必須である', function () {
    $data = validCustomerData();
    unset($data['name']);

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは文字列である必要がある', function () {
    $data = validCustomerData();
    $data['name'] = 123;

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('nameは100文字まで許可される', function () {
    $data = validCustomerData();
    $data['name'] = str_repeat('あ', 100);

    $validator = customerRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('nameが101文字の場合は許可されない', function () {
    $data = validCustomerData();
    $data['name'] = str_repeat('あ', 101);

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('emailは必須である', function () {
    $data = validCustomerData();
    unset($data['email']);

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('emailはメールアドレス形式である必要がある', function () {
    $data = validCustomerData();
    $data['email'] = 'invalid-email';

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('emailは255文字まで許可される', function () {
    $data = validCustomerData();

    $localPart = str_repeat('a', 243);
    $data['email'] = $localPart . '@example.com';

    $validator = customerRequestValidator($data);

    expect($validator->passes())->toBeTrue();
});

test('emailが256文字の場合は許可されない', function () {
    $data = validCustomerData();

    $localPart = str_repeat('a', 244);
    $data['email'] = $localPart . '@example.com';

    $validator = customerRequestValidator($data);

    expect($validator->errors()->has('email'))->toBeTrue();
});
