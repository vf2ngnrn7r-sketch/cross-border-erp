<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * 定时同步骨架：每小时同步订单，每日同步库存与财务
 * 实际生产请按店铺时区与 API 配额调整
 */
Schedule::command('amazon:sync --scope=orders --queue')->hourly();
Schedule::command('amazon:sync --scope=inventory --queue')->dailyAt('02:00');
Schedule::command('amazon:sync --scope=finance --queue')->dailyAt('03:00');
Schedule::command('amazon:sync --scope=listings --queue')->dailyAt('04:00');
