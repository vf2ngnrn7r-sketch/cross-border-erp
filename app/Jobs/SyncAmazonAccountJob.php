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
        public string $scope = 'all' // all|catalog|orders|inventory|listings|finance|ads|ad_metrics|returns|fba_inbound|messaging|product_qualifications|store_qualifications
    ) {}

    public function handle(AmazonSyncService $sync): void
    {
        $account = AmazonAccount::query()->find($this->amazonAccountId);
        if (! $account) {
            Log::warning('SyncAmazonAccountJob: account missing', ['id' => $this->amazonAccountId]);

            return;
        }

        match ($this->scope) {
            'catalog' => $sync->syncCatalog($account),
            'orders' => $sync->syncOrders($account),
            'inventory' => $sync->syncInventory($account),
            'listings' => $sync->syncListings($account),
            'finance' => $sync->syncFinance($account),
            'ads' => $sync->syncAdvertising($account),
            'ad_metrics' => $sync->syncAdPerformance($account),
            'returns' => $sync->syncReturns($account),
            'fba_inbound' => $sync->syncInboundShipments($account),
            'messaging' => $sync->syncMessaging($account),
            'product_qualifications' => $sync->syncProductQualifications($account),
            'store_qualifications' => $sync->syncStoreQualifications($account),
            default => $sync->syncAll($account),
        };
    }
}
