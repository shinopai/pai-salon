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
