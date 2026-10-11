<?php

use App\Enums\ReservationStatus;
use App\Enums\StaffRole;
use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

uses(Tests\TestCase::class, DatabaseMigrations::class);

test('同時実行でも同じ時間帯の予約は二重登録されない', function () {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('このテストはPostgreSQL専用です。');
    }

    $user = User::factory()->create();

    $staff = (new Staff())->forceFill([
        'user_id' => $user->id,
        'role' => StaffRole::STAFF,
        'name' => '競合テストスタッフ',
    ]);
    $staff->save();

    $menu = Menu::create([
        'name' => '競合テストメニュー',
        'duration' => 60,
    ]);

    DB::table('staff_menus')->insert([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $startAt = now()->addDays(7)->setTime(10, 0, 0);

    BusinessHour::create([
        'day_of_week' => $startAt->dayOfWeek,
        'open_time' => '09:00',
        'close_time' => '18:00',
        'is_closed' => false,
    ]);

    $connection = DB::connection();
    $processes = [];

    $childScript = <<<'PHP'
require getcwd().'/vendor/autoload.php';

$app = require getcwd().'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \Illuminate\Support\Facades\DB::selectOne(
        "SELECT set_config('application_name', ?, false)",
        [getenv('FT029_APP_NAME')]
    );

    $payload = json_decode(
        getenv('FT029_PAYLOAD'),
        true,
        flags: JSON_THROW_ON_ERROR
    );

    $reservation = app(\App\Services\ReservationService::class)
        ->reserve($payload);

    echo json_encode([
        'result' => 'success',
        'reservation_id' => $reservation->id,
    ]);
} catch (\Illuminate\Validation\ValidationException $e) {
    echo json_encode(['result' => 'conflict']);
} catch (\Throwable $e) {
    fwrite(STDERR, $e::class.': '.$e->getMessage());
    exit(1);
}
PHP;

    $database = config('database.connections.pgsql');

    $baseEnv = [
        'APP_ENV' => 'testing',
        'APP_KEY' => config('app.key'),
        'DB_CONNECTION' => 'pgsql',
        'DB_HOST' => $database['host'],
        'DB_PORT' => (string) $database['port'],
        'DB_DATABASE' => $database['database'],
        'DB_USERNAME' => $database['username'],
        'DB_PASSWORD' => $database['password'],
        'DB_URL' => '',
        'MAIL_MAILER' => 'array',
    ];

    $payload = json_encode([
        'staff_id' => $staff->id,
        'menu_id' => $menu->id,
        'start_at' => $startAt->toDateTimeString(),
        'customer_name' => '競合テスト顧客',
        'customer_email' => 'ft029@example.com',
    ], JSON_THROW_ON_ERROR);

    $connection->beginTransaction();

    try {
        // 親プロセスがスタッフ行をロックする。
        Staff::query()
            ->whereKey($staff->id)
            ->lockForUpdate()
            ->firstOrFail();

        // 独立したPHPプロセスで同じ予約を同時に試みる。
        for ($i = 0; $i < 2; $i++) {
            $env = array_merge($baseEnv, [
                'FT029_APP_NAME' => 'ft029_child_' . $i,
                'FT029_PAYLOAD' => $payload,
            ]);

            $process = new Process(
                [PHP_BINARY, '-r', $childScript],
                base_path(),
                $env
            );

            $process->setTimeout(30);
            $process->start();

            $processes[] = $process;
        }

        // 両プロセスがスタッフ行のロック待ちになるまで待つ。
        $deadline = microtime(true) + 15;
        $waiting = 0;

        do {
            // PostgreSQLの統計情報スナップショットを更新する。
            $connection->selectOne('SELECT pg_stat_clear_snapshot()');

            $waiting = (int) $connection->selectOne(
                <<<'SQL'
                    SELECT COUNT(*) AS aggregate
                    FROM pg_stat_activity
                    WHERE datname = current_database()
                      AND application_name IN (
                          'ft029_child_0',
                          'ft029_child_1'
                      )
                      AND wait_event_type = 'Lock'
                SQL
            )->aggregate;

            if ($waiting === 2) {
                break;
            }

            foreach ($processes as $index => $process) {
                if ($process->isTerminated()) {
                    throw new RuntimeException(sprintf(
                        "子プロセス%dがロック待ち前に終了しました。\n終了コード: %s\n標準出力: %s\n標準エラー: %s",
                        $index,
                        (string) $process->getExitCode(),
                        $process->getOutput(),
                        $process->getErrorOutput(),
                    ));
                }
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        if ($waiting !== 2) {
            throw new RuntimeException(
                '2つの予約処理がロック待ちになることを確認できませんでした。'
            );
        }

        // ロックを解放し、予約処理を競合させる。
        $connection->commit();

        foreach ($processes as $process) {
            $process->wait();
        }

        $results = [];

        foreach ($processes as $process) {
            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    '予約プロセスが異常終了しました: '
                        . $process->getErrorOutput()
                );
            }

            $results[] = json_decode(
                trim($process->getOutput()),
                true,
                flags: JSON_THROW_ON_ERROR
            );
        }

        expect(
            array_filter(
                $results,
                fn(array $result) => $result['result'] === 'success'
            )
        )->toHaveCount(1);

        expect(
            array_filter(
                $results,
                fn(array $result) => $result['result'] === 'conflict'
            )
        )->toHaveCount(1);

        expect(
            Reservation::query()
                ->where('staff_id', $staff->id)
                ->where('start_at', $startAt)
                ->where('status', '!=', ReservationStatus::CANCELLED)
                ->count()
        )->toBe(1);
    } finally {
        if ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $process->stop(1);
            }
        }
    }
});
