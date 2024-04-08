<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 24時間以上有効期限が切れているトークンのレコードを削除（毎日2:00に実行）
        $schedule->command('sanctum:prune-expired --hours=24')->dailyAt('2:00');

        /**
         * 有効期限が切れているトークンのレコード削除（毎秒実行）
         * $schedule->command('sanctum:prune-expired')->everySecond();
         *
         * 下記コマンドで削除/ログ記録できたこと確認済み
         * php artisan schedule:run >> ./storage/logs/schedule.log 2>&1
         */
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
