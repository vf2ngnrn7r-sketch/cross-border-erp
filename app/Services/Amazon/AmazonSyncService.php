<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AmazonAccount;
use App\Models\FinancialEvent;
use App\Models\Inventory;
use App\Models\Listing;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReturn;
use App\Models\ReportRequest;
use App\Models\Settlement;
use App\Models\SyncJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 亚马逊数据同步编排服务（调用 SP-API 客户端并将结果落库）
 */
class AmazonSyncService
{
    public function __construct(
        protected AmazonSpApiClient $client
    ) {}

    public function syncOrders(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'orders');
        $job->markRunning();

        try {
            $payload = $this->client->getOrders($account);
            $marketplace = $account->marketplaces()->first();

            foreach ($payload['Orders'] ?? [] as $row) {
                $order = Order::query()->updateOrCreate(
                    ['amazon_order_id' => $row['AmazonOrderId']],
                    [
                        'organization_id' => $account->organization_id,
                        'amazon_account_id' => $account->id,
                        'marketplace_id' => $marketplace?->id,
                        'purchase_date' => $row['PurchaseDate'] ?? null,
                        'order_status' => $row['OrderStatus'] ?? 'Pending',
                        'fulfillment_channel' => $row['FulfillmentChannel'] ?? null,
                        'sales_channel' => $row['SalesChannel'] ?? null,
                        'order_total' => $row['OrderTotal']['Amount'] ?? null,
                        'currency' => $row['OrderTotal']['CurrencyCode'] ?? 'USD',
                        'is_prime' => (bool) ($row['IsPrime'] ?? false),
                        'last_update_date' => $row['LastUpdateDate'] ?? null,
                    ]
                );

                if ($order->items()->count() === 0) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'order_item_id' => 'stub-item-'.$order->id,
                        'asin' => 'B0STUB0001',
                        'seller_sku' => 'SKU-DEMO-001',
                        'title' => 'Demo Wireless Earbuds',
                        'quantity_ordered' => 1,
                        'quantity_shipped' => 0,
                        'item_price' => $order->order_total,
                        'currency' => $order->currency,
                    ]);
                }
            }

            $account->forceFill(['last_synced_at' => now()])->save();
            $job->markSucceeded(['orders' => count($payload['Orders'] ?? [])]);
        } catch (\Throwable $e) {
            Log::error('syncOrders failed', ['error' => $e->getMessage()]);
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncInventory(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'inventory');
        $job->markRunning();

        try {
            $payload = $this->client->getInventory($account);
            $marketplace = $account->marketplaces()->first();

            foreach ($payload['inventorySummaries'] ?? [] as $row) {
                Inventory::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'sku' => $row['sellerSku'],
                        'fulfillment_channel' => $row['fulfillmentChannel'] ?? 'FBA',
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'marketplace_id' => $marketplace?->id,
                        'asin' => $row['asin'] ?? null,
                        'quantity_available' => $row['totalQuantity'] ?? 0,
                        'quantity_inbound' => $row['inboundQuantity'] ?? 0,
                        'quantity_reserved' => $row['reservedQuantity'] ?? 0,
                        'quantity_unfulfillable' => $row['unfulfillableQuantity'] ?? 0,
                        'synced_at' => now(),
                    ]
                );
            }

            $job->markSucceeded(['rows' => count($payload['inventorySummaries'] ?? [])]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncListings(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'listings');
        $job->markRunning();

        try {
            $payload = $this->client->getListings($account);
            $marketplace = $account->marketplaces()->first()
                ?? Marketplace::query()->where('code', 'ATVPDKIKX0DER')->first();

            foreach ($payload['items'] ?? [] as $row) {
                Listing::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'marketplace_id' => $marketplace->id,
                        'seller_sku' => $row['sku'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'asin' => $row['asin'] ?? null,
                        'title' => $row['title'] ?? null,
                        'price' => $row['price'] ?? null,
                        'currency' => $row['currency'] ?? 'USD',
                        'fulfillment_channel' => $row['fulfillmentChannel'] ?? 'FBA',
                        'status' => ($row['status'] ?? '') === 'BUYABLE' ? 'active' : 'inactive',
                        'synced_at' => now(),
                    ]
                );
            }

            $job->markSucceeded(['rows' => count($payload['items'] ?? [])]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncFinance(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'finance');
        $job->markRunning();

        try {
            $payload = $this->client->getFinancialEvents($account);

            $settlement = Settlement::query()->create([
                'organization_id' => $account->organization_id,
                'amazon_account_id' => $account->id,
                'settlement_id' => 'stub-settle-'.now()->format('Ymd'),
                'settlement_start' => now()->subDays(14),
                'settlement_end' => now(),
                'deposit_date' => now()->addDays(3),
                'total_amount' => 0,
                'currency' => 'USD',
                'status' => 'open',
            ]);

            $total = 0;
            foreach ($payload['FinancialEvents']['ShipmentEventList'] ?? [] as $event) {
                foreach ($event['ShipmentItemList'] ?? [] as $item) {
                    foreach ($item['ItemFeeList'] ?? [] as $fee) {
                        $amount = (float) ($fee['FeeAmount']['CurrencyAmount'] ?? 0);
                        $total += $amount;
                        FinancialEvent::query()->create([
                            'organization_id' => $account->organization_id,
                            'amazon_account_id' => $account->id,
                            'settlement_id' => $settlement->id,
                            'event_type' => 'Shipment',
                            'fee_type' => $fee['FeeType'] ?? null,
                            'amount' => $amount,
                            'currency' => $fee['FeeAmount']['CurrencyCode'] ?? 'USD',
                            'posted_date' => $event['PostedDate'] ?? now(),
                            'raw' => $fee,
                        ]);
                    }
                    foreach ($item['ItemChargeList'] ?? [] as $charge) {
                        $amount = (float) ($charge['ChargeAmount']['CurrencyAmount'] ?? 0);
                        $total += $amount;
                        FinancialEvent::query()->create([
                            'organization_id' => $account->organization_id,
                            'amazon_account_id' => $account->id,
                            'settlement_id' => $settlement->id,
                            'event_type' => 'Charge',
                            'fee_type' => $charge['ChargeType'] ?? null,
                            'amount' => $amount,
                            'currency' => $charge['ChargeAmount']['CurrencyCode'] ?? 'USD',
                            'posted_date' => $event['PostedDate'] ?? now(),
                            'raw' => $charge,
                        ]);
                    }
                }
            }

            $settlement->update(['total_amount' => $total]);
            $job->markSucceeded(['settlement_id' => $settlement->id, 'total' => $total]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncReturns(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'returns');
        $job->markRunning();

        try {
            $payload = $this->client->getReturns($account);
            foreach ($payload['returns'] ?? [] as $row) {
                ProductReturn::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'return_id' => $row['returnId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'asin' => $row['asin'] ?? null,
                        'seller_sku' => $row['sellerSku'] ?? null,
                        'reason' => $row['reason'] ?? null,
                        'status' => $row['status'] ?? 'requested',
                        'quantity' => $row['quantity'] ?? 1,
                        'return_date' => $row['returnDate'] ?? null,
                    ]
                );
            }
            $job->markSucceeded(['rows' => count($payload['returns'] ?? [])]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function requestReport(AmazonAccount $account, string $reportType, array $options = []): SyncJob
    {
        $job = $this->startJob($account, 'reports', $reportType);
        $job->markRunning();

        try {
            $created = $this->client->createReport($account, $reportType, $options);
            $status = $this->client->getReport($account, $created['reportId']);

            ReportRequest::query()->create([
                'organization_id' => $account->organization_id,
                'amazon_account_id' => $account->id,
                'sync_job_id' => $job->id,
                'external_report_id' => $created['reportId'],
                'report_type' => $reportType,
                'processing_status' => $status['processingStatus'] ?? 'DONE',
                'document_id' => $status['reportDocumentId'] ?? null,
                'request_options' => $options,
            ]);

            $job->markSucceeded(['reportId' => $created['reportId']]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncAll(AmazonAccount $account): array
    {
        return DB::transaction(function () use ($account) {
            return [
                'orders' => $this->syncOrders($account),
                'inventory' => $this->syncInventory($account),
                'listings' => $this->syncListings($account),
                'finance' => $this->syncFinance($account),
                'returns' => $this->syncReturns($account),
            ];
        });
    }

    protected function startJob(AmazonAccount $account, string $type, ?string $reportType = null): SyncJob
    {
        return SyncJob::query()->create([
            'organization_id' => $account->organization_id,
            'amazon_account_id' => $account->id,
            'job_type' => $type,
            'report_type' => $reportType,
            'status' => 'pending',
        ]);
    }
}
