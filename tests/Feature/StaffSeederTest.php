<?php

use App\Models\Staff;
use App\Models\User;
use Database\Seeders\StaffSeeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

beforeEach(function () {
    $this->originalAppEnvironment = app()->environment();

    $name = 'PAISALON_INITIAL_STAFF_PASSWORD';

    $this->originalPasswordEnvironment = [
        'getenv' => getenv($name),
        '_ENV_exists' => array_key_exists($name, $_ENV),
        '_ENV' => $_ENV[$name] ?? null,
        '_SERVER_exists' => array_key_exists($name, $_SERVER),
        '_SERVER' => $_SERVER[$name] ?? null,
    ];
});

afterEach(function () {
    $name = 'PAISALON_INITIAL_STAFF_PASSWORD';
    $original = $this->originalPasswordEnvironment;

    if ($original['getenv'] === false) {
        putenv($name);
    } else {
        putenv($name . '=' . $original['getenv']);
    }

    if ($original['_ENV_exists']) {
        $_ENV[$name] = $original['_ENV'];
    } else {
        unset($_ENV[$name]);
    }

    if ($original['_SERVER_exists']) {
        $_SERVER[$name] = $original['_SERVER'];
    } else {
        unset($_SERVER[$name]);
    }

    app()['env'] = $this->originalAppEnvironment;
});

/**
 * 環境変数を設定する。
 */
function setInitialStaffPasswordEnvironment(?string $password): void
{
    $name = 'PAISALON_INITIAL_STAFF_PASSWORD';

    if ($password === null) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);

        return;
    }

    putenv($name . '=' . $password);
    $_ENV[$name] = $password;
    $_SERVER[$name] = $password;
}

/**
 * アプリケーションを本番環境として扱う。
 */
function setProductionApplicationEnvironment(): void
{
    app()['env'] = 'production';
}

test('開発環境では初期ユーザーとスタッフが作成される', function () {
    app(StaffSeeder::class)->run();

    expect(User::count())->toBe(4)
        ->and(Staff::count())->toBe(4);

    $user = User::where('email', 'sato@example.com')->firstOrFail();

    expect(Hash::check('password', $user->password))->toBeTrue();
});

test('Seederを再実行しても重複登録されず既存のパスワードも変更されない', function () {
    app(StaffSeeder::class)->run();

    $user = User::where('email', 'sato@example.com')->firstOrFail();

    $user->password = Hash::make('changed-password');
    $user->save();

    $originalPasswordHash = $user->fresh()->password;

    app(StaffSeeder::class)->run();

    expect(User::count())->toBe(4)
        ->and(Staff::count())->toBe(4)
        ->and(
            User::where('email', 'sato@example.com')
                ->firstOrFail()
                ->password
        )->toBe($originalPasswordHash);
});

test('本番環境では環境変数のパスワードが使用される', function () {
    setProductionApplicationEnvironment();

    expect(app()->environment('production'))->toBeTrue();

    setInitialStaffPasswordEnvironment('production-test-password');

    app(StaffSeeder::class)->run();

    $user = User::where('email', 'sato@example.com')->firstOrFail();

    expect(Hash::check(
        'production-test-password',
        $user->password
    ))->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeFalse();
});

test('本番環境で環境変数が未設定の場合は例外が発生する', function () {
    setProductionApplicationEnvironment();

    expect(app()->environment('production'))->toBeTrue();

    setInitialStaffPasswordEnvironment(null);

    expect(fn() => app(StaffSeeder::class)->run())
        ->toThrow(
            RuntimeException::class,
            '本番環境では PAISALON_INITIAL_STAFF_PASSWORD の設定が必要です。'
        );

    expect(User::count())->toBe(0)
        ->and(Staff::count())->toBe(0);
});

test('本番環境で環境変数が空文字の場合は例外が発生する', function () {
    setProductionApplicationEnvironment();

    expect(app()->environment('production'))->toBeTrue();

    setInitialStaffPasswordEnvironment('');

    expect(fn() => app(StaffSeeder::class)->run())
        ->toThrow(
            RuntimeException::class,
            '本番環境では PAISALON_INITIAL_STAFF_PASSWORD の設定が必要です。'
        );

    expect(User::count())->toBe(0)
        ->and(Staff::count())->toBe(0);
});
