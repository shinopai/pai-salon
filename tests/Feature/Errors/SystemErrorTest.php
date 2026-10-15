<?php

use Illuminate\Support\Facades\Route;

test('予期しない例外の詳細がログに記録される', function () {
    config(['app.debug' => false]);

    Route::get('/test-system-error-log', function () {
        throw new RuntimeException('テスト用のログエラー');
    });

    $this->get('/test-system-error-log');

    expect(file_get_contents(storage_path('logs/laravel.log')))
        ->toContain('テスト用のログエラー');
});

test('予期しない例外が発生した場合はシステムエラー画面を表示する', function () {
    config(['app.debug' => false]);

    Route::get('/test-system-error', function () {
        throw new RuntimeException('テスト用の内部エラー');
    });

    $response = $this->get('/test-system-error');

    $response->assertStatus(500);
    $response->assertSee('500');
    $response->assertSee('エラーが発生しました');
    $response->assertSee('時間をおいて、もう一度お試しください。');
    $response->assertDontSee('テスト用の内部エラー');
});

test('データベース例外の詳細がログに記録される', function () {
    config(['app.debug' => false]);

    $logPath = storage_path('logs/laravel.log');
    $originalSize = file_exists($logPath) ? filesize($logPath) : 0;

    Route::get('/test-database-error-log', function () {
        throw new \Illuminate\Database\QueryException(
            'test_connection',
            'select * from test_table',
            [],
            new \PDOException('テスト用のデータベース接続エラー'),
        );
    });

    $response = $this->get('/test-database-error-log');

    $response->assertStatus(500);

    clearstatcache(true, $logPath);
    $log = file_exists($logPath)
        ? file_get_contents($logPath, false, null, $originalSize)
        : '';

    expect($log)
        ->toContain('テスト用のデータベース接続エラー');
});

test('予期しない例外のログにセッション情報やDB認証情報が含まれない', function () {
    config(['app.debug' => false]);

    $logPath = storage_path('logs/laravel.log');
    $originalSize = file_exists($logPath) ? filesize($logPath) : 0;

    $sensitiveValues = [
        'ERR013_TEST_DB_PASSWORD_7xK9',
        'ERR013_TEST_DB_USERNAME_4pR2',
        'ERR013_TEST_SESSION_SECRET_8mQ5',
    ];

    config([
        'database.connections.mysql.username' => $sensitiveValues[1],
        'database.connections.mysql.password' => $sensitiveValues[0],
    ]);

    session(['err013_test_session_secret' => $sensitiveValues[2]]);

    Route::get('/test-sensitive-error-log', function () {
        throw new RuntimeException('ERR013_TEST_EXCEPTION');
    });

    $response = $this->get('/test-sensitive-error-log');

    $response->assertStatus(500);

    clearstatcache(true, $logPath);

    $log = file_exists($logPath)
        ? file_get_contents($logPath, false, null, $originalSize)
        : '';

    foreach ($sensitiveValues as $sensitiveValue) {
        expect($log)->not->toContain($sensitiveValue);
    }
});
