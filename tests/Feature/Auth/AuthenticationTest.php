<?php

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

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

test('ユーザーは不正なパスワードでは認証できずログにも記録されない', function () {
    Log::shouldReceive('error')->never();
    Log::shouldReceive('warning')->never();
    Log::shouldReceive('info')->never();
    Log::shouldReceive('debug')->never();
    Log::shouldReceive('notice')->never();
    Log::shouldReceive('alert')->never();
    Log::shouldReceive('critical')->never();
    Log::shouldReceive('emergency')->never();

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret-password-for-log-check',
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

test('ログインに5回失敗するとレート制限される', function () {
    Event::fake([Lockout::class]);

    $user = User::factory()->create();
    $email = $user->email;

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();

    Event::assertDispatched(Lockout::class);

    $response->assertSessionHasErrors('email');
});

test('ログインに成功すると失敗回数がリセットされる', function () {
    $user = User::factory()->create();

    $key = strtolower($user->email) . '|' . $this->app['request']->ip();
    $key = \Illuminate\Support\Str::transliterate($key);

    RateLimiter::hit($key);
    RateLimiter::hit($key);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/staff/dashboard');

    expect(RateLimiter::attempts($key))->toBe(0);
});
