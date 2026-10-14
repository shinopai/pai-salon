<?php

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;

test('ログイン画面を表示できる', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('ユーザーはログイン画面から認証できる', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect('/staff/dashboard');
});

test('ユーザーは不正なパスワードでは認証できない', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('ユーザーはログアウトできる', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();

    $response->assertRedirect('/');
});

test('管理者はログイン後に管理者ダッシュボードへ遷移できる', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $staff = new Staff();

    $staff->forceFill([
        'user_id' => $user->id,
        'name' => 'テスト管理者',
        'role' => StaffRole::ADMIN,
    ])->save();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect('/admin/dashboard');
});
