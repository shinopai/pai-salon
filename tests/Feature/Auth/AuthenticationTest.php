<?php

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
