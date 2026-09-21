<?php

use App\Enums\StaffRole;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\User;

test('管理者顧客一覧を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.index')
    );

    $response->assertOk();

    $response->assertViewIs('admin.customers.index');

    $response->assertViewHas('customers', function ($customers) use ($customer) {
        return $customers->contains($customer);
    });

    $response->assertSee('山田 太郎');
    $response->assertSee('yamada@example.com');
});

test('管理者は顧客詳細を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.show', $customer)
    );

    $response->assertOk();

    $response->assertViewIs('admin.customers.show');

    $response->assertViewHas('customer', $customer);

    $response->assertSee('山田 太郎');
    $response->assertSee('yamada@example.com');
});

test('管理者は顧客編集画面を表示できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.edit', $customer)
    );

    $response->assertOk();

    $response->assertViewIs('admin.customers.edit');

    $response->assertViewHas('customer', $customer);

    $response->assertSee('山田 太郎');
    $response->assertSee('yamada@example.com');
});

test('管理者は顧客情報を更新できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => '佐藤 花子',
            'email' => 'sato@example.com',
        ]
    );

    $response->assertRedirect(
        route('admin.customers.show', $customer)
    );

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '佐藤 花子',
        'email' => 'sato@example.com',
    ]);
});

test('管理者は顧客更新時に名前が未入力の場合バリデーションエラーになる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => '',
            'email' => 'sato@example.com',
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);
});

test('管理者は顧客更新時に名前が100文字を超える場合バリデーションエラーになる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => str_repeat('あ', 101),
            'email' => 'sato@example.com',
        ]
    );

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);
});

test('管理者は顧客更新時にメールアドレスが未入力の場合バリデーションエラーになる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => '佐藤 花子',
            'email' => '',
        ]
    );

    $response->assertSessionHasErrors('email');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);
});

test('管理者は顧客更新時にメールアドレス形式が不正な場合バリデーションエラーになる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => '佐藤 花子',
            'email' => 'invalid-email',
        ]
    );

    $response->assertSessionHasErrors('email');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);
});

test('管理者は顧客更新時にメールアドレスが255文字を超える場合バリデーションエラーになる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $customer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $email = str_repeat('a', 244) . '@example.com';

    $this->actingAs($user);

    $response = $this->put(
        route('admin.customers.update', $customer),
        [
            'name' => '佐藤 花子',
            'email' => $email,
        ]
    );

    $response->assertSessionHasErrors('email');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);
});

test('管理者はkeywordで顧客を検索できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $targetCustomer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $otherCustomer = Customer::create([
        'name' => '佐藤 花子',
        'email' => 'sato@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.index', [
            'keyword' => '山田',
        ])
    );

    $response->assertOk();

    $response->assertViewIs('admin.customers.index');

    $response->assertViewHas('customers', function ($customers) use ($targetCustomer, $otherCustomer) {
        return $customers->contains($targetCustomer)
            && ! $customers->contains($otherCustomer);
    });

    $response->assertSee('山田 太郎');
    $response->assertDontSee('佐藤 花子');
});

test('管理者はkeywordでメールアドレスから顧客を検索できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $targetCustomer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $otherCustomer = Customer::create([
        'name' => '佐藤 花子',
        'email' => 'sato@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.index', [
            'keyword' => 'yamada@example.com',
        ])
    );

    $response->assertOk();

    $response->assertViewIs('admin.customers.index');

    $response->assertViewHas('customers', function ($customers) use ($targetCustomer, $otherCustomer) {
        return $customers->contains($targetCustomer)
            && ! $customers->contains($otherCustomer);
    });

    $response->assertSee('山田 太郎');
    $response->assertDontSee('佐藤 花子');
});

test('管理者は顧客名の一部をkeywordに指定して検索できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $targetCustomer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $otherCustomer = Customer::create([
        'name' => '佐藤 花子',
        'email' => 'sato@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.index', [
            'keyword' => '田 太',
        ])
    );

    $response->assertOk();

    $response->assertViewHas('customers', function ($customers) use ($targetCustomer, $otherCustomer) {
        return $customers->contains($targetCustomer)
            && ! $customers->contains($otherCustomer);
    });

    $response->assertSee('山田 太郎');
    $response->assertDontSee('佐藤 花子');
});

test('管理者はメールアドレスの一部をkeywordに指定して検索できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    Staff::forceCreate([
        'user_id' => $user->id,
        'name' => '管理者スタッフ',
        'role' => StaffRole::ADMIN,
    ]);

    $targetCustomer = Customer::create([
        'name' => '山田 太郎',
        'email' => 'yamada@example.com',
    ]);

    $otherCustomer = Customer::create([
        'name' => '佐藤 花子',
        'email' => 'sato@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('admin.customers.index', [
            'keyword' => 'yamada',
        ])
    );

    $response->assertOk();

    $response->assertViewHas('customers', function ($customers) use ($targetCustomer, $otherCustomer) {
        return $customers->contains($targetCustomer)
            && ! $customers->contains($otherCustomer);
    });

    $response->assertSee('山田 太郎');
    $response->assertDontSee('佐藤 花子');
});
