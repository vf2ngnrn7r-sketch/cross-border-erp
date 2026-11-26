<?php

namespace App\Console\Commands;

use App\Models\AmazonAccount;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Console\Command;

/** artisan amazon:report — 创建报表请求（桩） */
class RequestAmazonReportCommand extends Command
{
    protected $signature = 'amazon:report
        {--account= : amazon_accounts.id（必填）}
        {--type=GET_FLAT_FILE_ALL_ORDERS_DATA_BY_ORDER_DATE_GENERAL : 报表类型}';

    protected $description = '向 SP-API 请求报表（桩模式立即完成）';

    public function handle(AmazonSyncService $sync): int
    {
        $accountId = $this->option('account');
        if (! $accountId) {
            $this->error('请使用 --account= 指定账号 ID');

            return self::FAILURE;
        }

        $account = AmazonAccount::query()->find($accountId);
        if (! $account) {
            $this->error('账号不存在');

            return self::FAILURE;
        }

        $job = $sync->requestReport($account, (string) $this->option('type'));
        $this->info("报表任务 #{$job->id} 状态：{$job->status}");

        return self::SUCCESS;
    }
}
