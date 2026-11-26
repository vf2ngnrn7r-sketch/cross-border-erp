<?php

namespace App\Jobs;

use App\Models\AmazonAccount;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/** 队列任务：同步单个亚马逊账号的核心数据 */
class SyncAmazonAccountJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $amazonAccountId,
        public string $scope = 'all' // all|orders|inventory|listings|finance|returns
    ) {}

    public function handle(AmazonSyncService $sync): void
    {
        $account = AmazonAccount::query()->find($this->amazonAccountId);
        if (! $account) {
            Log::warning('SyncAmazonAccountJob: account missing', ['id' => $this->amazonAccountId]);

            return;
        }

        match ($this->scope) {
            'orders' => $sync->syncOrders($account),
            'inventory' => $sync->syncInventory($account),
            'listings' => $sync->syncListings($account),
            'finance' => $sync->syncFinance($account),
            'returns' => $sync->syncReturns($account),
            default => $sync->syncAll($account),
        };
    }
}
