<?php

use App\Enums\StaffRole;
use App\Http\Requests\AdminStaffUpdateRequest;
use App\Models\Staff;
use Illuminate\Contracts\Validation\Validator;

function AdminStaffUpdateRequestValidator(array $data): Validator
{
    $request = new AdminStaffUpdateRequest();

    $request->merge($data);

    return app()->make('validator')->make(
        $request->all(),
        $request->rules()
    );
}

test('氏名・メールアドレス・権限が有効ならバリデーションを通過する', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'name' => '更新後スタッフ',
        'email' => 'updated@example.com',
        'role' => StaffRole::STAFF->value,
    ]);

    expect($validator->passes())->toBeTrue();
});

test('氏名が未入力ならバリデーションエラーになる', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'email' => 'updated@example.com',
        'role' => StaffRole::STAFF->value,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('メールアドレスが未入力ならバリデーションエラーになる', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'name' => '更新後スタッフ',
        'role' => StaffRole::STAFF->value,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('メールアドレスの形式が不正ならバリデーションエラーになる', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'name' => '更新後スタッフ',
        'email' => 'invalid-email',
        'role' => StaffRole::STAFF->value,
    ]);

    expect($validator->fails())->toBeTrue();
});

test('権限が未入力ならバリデーションエラーになる', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'name' => '更新後スタッフ',
        'email' => 'updated@example.com',
    ]);

    expect($validator->fails())->toBeTrue();
});

test('権限が不正ならバリデーションエラーになる', function () {
    $validator = AdminStaffUpdateRequestValidator([
        'name' => '更新後スタッフ',
        'email' => 'updated@example.com',
        'role' => 'invalid-role',
    ]);

    expect($validator->fails())->toBeTrue();
});

// test('現在のメールアドレスはバリデーションを通過する', function () {
//     $user = User::factory()->create([
//         'email' => 'staff@example.com',
//     ]);

//     $staff = Staff::forceCreate([
//         'user_id' => $user->id,
//         'name' => 'テストスタッフ',
//         'role' => StaffRole::STAFF,
//     ]);

//     $request = new AdminStaffUpdateRequest();

//     $request->setRouteResolver(function () use ($staff) {
//         return new class($staff)
//         {
//             public function __construct(
//                 private Staff $staff
//             ) {}

//             public function parameter(string $key)
//             {
//                 return $this->staff;
//             }
//         };
//     });

//     $validator = app()->make('validator')->make(
//         [
//             'name' => '更新後スタッフ',
//             'email' => 'staff@example.com',
//             'role' => StaffRole::STAFF->value,
//         ],
//         $request->rules()
//     );

//     expect($validator->passes())->toBeTrue();
// });
