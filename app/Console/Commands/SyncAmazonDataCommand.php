<?php

namespace App\Console\Commands;

use App\Jobs\SyncAmazonAccountJob;
use App\Models\AmazonAccount;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Console\Command;

/** artisan amazon:sync — 同步亚马逊账号数据 */
class SyncAmazonDataCommand extends Command
{
    protected $signature = 'amazon:sync
        {--account= : 指定 amazon_accounts.id}
        {--scope=all : all|catalog|orders|inventory|listings|finance|ads|ad_metrics|returns|fba_inbound|messaging|product_qualifications|store_qualifications}
        {--queue : 投递到队列而非同步执行}';

    protected $description = '同步亚马逊店铺数据（默认 Fixture 驱动，无真实 API）';

    public function handle(AmazonSyncService $sync): int
    {
        $this->comment('Amazon driver: '.config('amazon.driver'));

        $query = AmazonAccount::query()->where('status', 'active');
        if ($id = $this->option('account')) {
            $query->whereKey($id);
        }

        $accounts = $query->get();
        if ($accounts->isEmpty()) {
            $this->warn('没有可同步的亚马逊账号。');

            return self::SUCCESS;
        }

        $scope = (string) $this->option('scope');
        foreach ($accounts as $account) {
            $this->info("同步账号 #{$account->id} {$account->name}（scope={$scope}）…");

            if ($this->option('queue')) {
                SyncAmazonAccountJob::dispatch($account->id, $scope);
                $this->line('  → 已投递队列');
                continue;
            }

            $result = match ($scope) {
                'catalog' => ['catalog' => $sync->syncCatalog($account)],
                'orders' => ['orders' => $sync->syncOrders($account)],
                'inventory' => ['inventory' => $sync->syncInventory($account)],
                'listings' => ['listings' => $sync->syncListings($account)],
                'finance' => ['finance' => $sync->syncFinance($account)],
                'ads' => ['ads' => $sync->syncAdvertising($account)],
                'ad_metrics' => ['ad_metrics' => $sync->syncAdPerformance($account)],
                'returns' => ['returns' => $sync->syncReturns($account)],
                'fba_inbound' => ['fba_inbound' => $sync->syncInboundShipments($account)],
                'messaging' => ['messaging' => $sync->syncMessaging($account)],
                'product_qualifications' => ['product_qualifications' => $sync->syncProductQualifications($account)],
                'store_qualifications' => ['store_qualifications' => $sync->syncStoreQualifications($account)],
                default => $sync->syncAll($account),
            };

            foreach ($result as $type => $job) {
                $this->line("  → {$type}: {$job->status}");
            }
        }

        return self::SUCCESS;
    }
}
