<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AdCampaign;
use App\Models\AdGroup;
use App\Models\AmazonAccount;
use App\Models\AToZClaim;
use App\Models\CustomerFeedback;
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
 * 亚马逊数据同步编排：经 AmazonSpApiClient（默认 Fixture）拉取并落库
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
                        'ship_service_level' => $row['ShipServiceLevel'] ?? null,
                        'order_total' => $row['OrderTotal']['Amount'] ?? null,
                        'currency' => $row['OrderTotal']['CurrencyCode'] ?? 'USD',
                        'buyer_email' => $row['BuyerInfo']['BuyerEmail'] ?? null,
                        'shipping_address' => $row['ShippingAddress'] ?? null,
                        'payment_method' => $row['PaymentMethod'] ?? null,
                        'is_prime' => (bool) ($row['IsPrime'] ?? false),
                        'last_update_date' => $row['LastUpdateDate'] ?? null,
                    ]
                );

                $itemsPayload = $this->client->getOrderItems($account, $order->amazon_order_id);
                foreach ($itemsPayload['OrderItems'] ?? [] as $item) {
                    OrderItem::query()->updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'order_item_id' => $item['OrderItemId'] ?? null,
                        ],
                        [
                            'asin' => $item['ASIN'] ?? null,
                            'seller_sku' => $item['SellerSKU'] ?? null,
                            'title' => $item['Title'] ?? null,
                            'quantity_ordered' => $item['QuantityOrdered'] ?? 0,
                            'quantity_shipped' => $item['QuantityShipped'] ?? 0,
                            'item_price' => $item['ItemPrice']['Amount'] ?? null,
                            'shipping_price' => $item['ShippingPrice']['Amount'] ?? null,
                            'item_tax' => $item['ItemTax']['Amount'] ?? null,
                            'promotion_discount' => $item['PromotionDiscount']['Amount'] ?? null,
                            'currency' => $item['ItemPrice']['CurrencyCode'] ?? $order->currency,
                        ]
                    );
                }
            }

            $account->forceFill(['last_synced_at' => now()])->save();
            $job->markSucceeded([
                'orders' => count($payload['Orders'] ?? []),
                'driver' => $payload['_meta']['driver'] ?? config('amazon.driver'),
            ]);
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
                        'quantity_available' => (int) ($row['totalQuantity'] ?? 0),
                        'quantity_inbound' => (int) ($row['inboundQuantity'] ?? 0),
                        'quantity_reserved' => (int) ($row['reservedQuantity'] ?? 0),
                        'quantity_unfulfillable' => (int) ($row['unfulfillableQuantity'] ?? 0),
                        'warehouse_location' => $row['fnSku'] ?? null,
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
            $events = $payload['FinancialEvents'] ?? [];

            $settlement = Settlement::query()->create([
                'organization_id' => $account->organization_id,
                'amazon_account_id' => $account->id,
                'settlement_id' => 'fixture-settle-'.now()->format('Ymd-His'),
                'settlement_start' => now()->subDays(14),
                'settlement_end' => now(),
                'deposit_date' => now()->addDays(3),
                'total_amount' => 0,
                'currency' => 'USD',
                'status' => 'open',
            ]);

            $total = 0;
            $total += $this->ingestShipmentEvents($account, $settlement, $events['ShipmentEventList'] ?? [], 'Shipment');
            $total += $this->ingestShipmentEvents($account, $settlement, $events['RefundEventList'] ?? [], 'Refund', true);
            $total += $this->ingestServiceFees($account, $settlement, $events['ServiceFeeEventList'] ?? []);

            $settlement->update(['total_amount' => $total]);
            $job->markSucceeded(['settlement_id' => $settlement->id, 'total' => $total]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncAdvertising(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'ads');
        $job->markRunning();

        try {
            $payload = $this->client->getAdCampaigns($account);
            $marketplace = $account->marketplaces()->first();
            $count = 0;

            foreach ($payload['campaigns'] ?? [] as $row) {
                $type = match ($row['campaignType'] ?? 'sp') {
                    'sponsoredProducts' => 'sp',
                    'sponsoredBrands' => 'sb',
                    'sponsoredDisplay' => 'sd',
                    default => substr((string) ($row['campaignType'] ?? 'sp'), 0, 8),
                };

                $campaign = AdCampaign::query()->updateOrCreate(
                    [
                        'organization_id' => $account->organization_id,
                        'external_campaign_id' => (string) ($row['campaignId'] ?? ''),
                    ],
                    [
                        'amazon_account_id' => $account->id,
                        'marketplace_id' => $marketplace?->id,
                        'name' => $row['name'] ?? 'Untitled',
                        'campaign_type' => $type,
                        'targeting_type' => $row['targetingType'] ?? null,
                        'state' => $row['state'] ?? 'enabled',
                        'daily_budget' => $row['dailyBudget'] ?? null,
                        'start_date' => $row['startDate'] ?? null,
                        'end_date' => $row['endDate'] ?? null,
                    ]
                );

                foreach ($row['adGroups'] ?? [] as $ag) {
                    AdGroup::query()->updateOrCreate(
                        [
                            'ad_campaign_id' => $campaign->id,
                            'external_ad_group_id' => (string) ($ag['adGroupId'] ?? ''),
                        ],
                        [
                            'name' => $ag['name'] ?? 'Ad Group',
                            'state' => $ag['state'] ?? 'enabled',
                            'default_bid' => $ag['defaultBid'] ?? null,
                        ]
                    );
                }
                $count++;
            }

            $job->markSucceeded(['campaigns' => $count]);
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
                $orderId = null;
                if (! empty($row['orderId'])) {
                    $orderId = Order::query()->where('amazon_order_id', $row['orderId'])->value('id');
                }

                ProductReturn::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'return_id' => $row['returnId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'order_id' => $orderId,
                        'asin' => $row['asin'] ?? null,
                        'seller_sku' => $row['sellerSku'] ?? null,
                        'reason' => $row['reason'] ?? null,
                        'status' => $row['status'] ?? 'requested',
                        'quantity' => $row['quantity'] ?? 1,
                        'return_date' => $row['returnDate'] ?? null,
                    ]
                );
            }

            foreach ($payload['aToZClaims'] ?? [] as $row) {
                $orderId = null;
                if (! empty($row['orderId'])) {
                    $orderId = Order::query()->where('amazon_order_id', $row['orderId'])->value('id');
                }

                AToZClaim::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'claim_id' => $row['claimId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'order_id' => $orderId,
                        'reason' => $row['reason'] ?? null,
                        'status' => $row['status'] ?? 'open',
                        'opened_at' => $row['openedAt'] ?? null,
                        'resolved_at' => $row['resolvedAt'] ?? null,
                    ]
                );
            }

            foreach ($payload['customerFeedback'] ?? [] as $row) {
                $orderId = null;
                if (! empty($row['orderId'])) {
                    $orderId = Order::query()->where('amazon_order_id', $row['orderId'])->value('id');
                }

                CustomerFeedback::query()->updateOrCreate(
                    [
                        'organization_id' => $account->organization_id,
                        'amazon_account_id' => $account->id,
                        'order_id' => $orderId,
                        'feedback_date' => $row['feedbackDate'] ?? now(),
                    ],
                    [
                        'rating' => $row['rating'] ?? null,
                        'comments' => $row['comments'] ?? null,
                    ]
                );
            }

            $job->markSucceeded([
                'returns' => count($payload['returns'] ?? []),
                'claims' => count($payload['aToZClaims'] ?? []),
                'feedback' => count($payload['customerFeedback'] ?? []),
            ]);
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

            $job->markSucceeded(['reportId' => $created['reportId'], 'driver' => 'fixture']);
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
                'ads' => $this->syncAdvertising($account),
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

    protected function ingestShipmentEvents(
        AmazonAccount $account,
        Settlement $settlement,
        array $eventList,
        string $eventType,
        bool $adjustment = false
    ): float {
        $total = 0.0;

        foreach ($eventList as $event) {
            $itemsKey = $adjustment ? 'ShipmentItemAdjustmentList' : 'ShipmentItemList';
            foreach ($event[$itemsKey] ?? [] as $item) {
                $chargeKey = $adjustment ? 'ItemChargeAdjustmentList' : 'ItemChargeList';
                $feeKey = $adjustment ? 'ItemFeeAdjustmentList' : 'ItemFeeList';

                foreach ($item[$chargeKey] ?? [] as $charge) {
                    $amount = (float) ($charge['ChargeAmount']['CurrencyAmount'] ?? 0);
                    $total += $amount;
                    $this->storeFinancialEvent($account, $settlement, $eventType, $charge['ChargeType'] ?? null, $amount, $charge, $event);
                }
                foreach ($item[$feeKey] ?? [] as $fee) {
                    $amount = (float) ($fee['FeeAmount']['CurrencyAmount'] ?? 0);
                    $total += $amount;
                    $this->storeFinancialEvent($account, $settlement, $eventType, $fee['FeeType'] ?? null, $amount, $fee, $event);
                }
            }
        }

        return $total;
    }

    protected function ingestServiceFees(AmazonAccount $account, Settlement $settlement, array $list): float
    {
        $total = 0.0;
        foreach ($list as $event) {
            foreach ($event['FeeList'] ?? [] as $fee) {
                $amount = (float) ($fee['FeeAmount']['CurrencyAmount'] ?? 0);
                $total += $amount;
                $this->storeFinancialEvent(
                    $account,
                    $settlement,
                    'ServiceFee',
                    $fee['FeeType'] ?? ($event['FeeReason'] ?? null),
                    $amount,
                    $fee,
                    $event
                );
            }
        }

        return $total;
    }

    protected function storeFinancialEvent(
        AmazonAccount $account,
        Settlement $settlement,
        string $eventType,
        ?string $feeType,
        float $amount,
        array $raw,
        array $event
    ): void {
        FinancialEvent::query()->create([
            'organization_id' => $account->organization_id,
            'amazon_account_id' => $account->id,
            'settlement_id' => $settlement->id,
            'event_type' => $eventType,
            'fee_type' => $feeType,
            'amount' => $amount,
            'currency' => $raw['FeeAmount']['CurrencyCode']
                ?? $raw['ChargeAmount']['CurrencyCode']
                ?? 'USD',
            'posted_date' => $event['PostedDate'] ?? now(),
            'raw' => $raw,
        ]);
    }
}
