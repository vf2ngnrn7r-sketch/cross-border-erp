<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AdCampaign;
use App\Models\AdGroup;
use App\Models\AdPerformanceMetric;
use App\Models\AmazonAccount;
use App\Models\AToZClaim;
use App\Models\BuyerMessage;
use App\Models\CustomerFeedback;
use App\Models\FinancialEvent;
use App\Models\Inventory;
use App\Models\Listing;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductQualification;
use App\Models\ProductReturn;
use App\Models\StoreQualification;
use App\Models\ReportRequest;
use App\Models\Settlement;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\SupportCase;
use App\Models\SyncJob;
use App\Models\Warehouse;
use Carbon\Carbon;
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

    public function syncCatalog(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'catalog');
        $job->markRunning();

        try {
            $payload = $this->client->getCatalogItems($account);
            $orgId = $account->organization_id;

            foreach ($payload['categories'] ?? [] as $cat) {
                ProductCategory::query()->updateOrCreate(
                    [
                        'organization_id' => $orgId,
                        'browse_node_id' => (string) ($cat['browseNodeId'] ?? $cat['name']),
                    ],
                    [
                        'name' => $cat['name'] ?? 'Unnamed',
                        'path' => $cat['path'] ?? null,
                        'amazon_product_type' => $cat['amazonProductType'] ?? null,
                        'attribute_schema' => $cat['attributeSchema'] ?? null,
                    ]
                );
            }

            // 先写 parent，再写 child / standalone
            $items = collect($payload['items'] ?? []);
            $ordered = $items->sortBy(fn ($i) => match ($i['role'] ?? 'standalone') {
                'parent' => 0,
                'standalone' => 1,
                default => 2,
            });

            $byAsin = [];
            foreach ($ordered as $row) {
                $summary = $row['summaries'][0] ?? [];
                $browse = $summary['browseClassification'] ?? [];
                $role = $row['role'] ?? 'standalone';
                $sku = $row['sku'] ?? ('ASIN-'.($row['asin'] ?? uniqid()));
                $title = $summary['itemName']
                    ?? ($row['attributes']['item_name'][0]['value'] ?? $sku);
                $attrs = [];
                foreach (['color', 'size', 'brand'] as $key) {
                    if (! empty($row['attributes'][$key][0]['value'])) {
                        $attrs[$key] = $row['attributes'][$key][0]['value'];
                    }
                }
                if (! empty($row['attributes']['color']['value'] ?? null)) {
                    $attrs['color'] = $row['attributes']['color']['value'];
                }

                $parentId = null;
                if ($role === 'child') {
                    $parentAsin = $row['parentAsin'] ?? null;
                    $parentId = $parentAsin && isset($byAsin[$parentAsin])
                        ? $byAsin[$parentAsin]->id
                        : Product::query()
                            ->where('organization_id', $orgId)
                            ->where('asin', $parentAsin)
                            ->value('id');
                }

                $product = Product::query()->updateOrCreate(
                    ['organization_id' => $orgId, 'sku' => $sku],
                    [
                        'parent_id' => $parentId,
                        'product_type' => $role === 'parent' ? 'parent' : ($role === 'child' ? 'child' : 'standalone'),
                        'parent_sku' => $row['parentSku'] ?? null,
                        'asin' => $row['asin'] ?? null,
                        'parent_asin' => $row['parentAsin'] ?? null,
                        'title' => $title,
                        'brand' => $summary['brandName']
                            ?? ($row['attributes']['brand'][0]['value'] ?? null),
                        'category' => $browse['displayName'] ?? null,
                        'browse_node_id' => $browse['classificationId'] ?? null,
                        'browse_node_name' => $browse['displayName'] ?? null,
                        'amazon_product_type' => $row['productTypes'][0]['productType'] ?? null,
                        'description' => $row['description'] ?? null,
                        'bullet_points' => $row['bulletPoints'] ?? null,
                        'variation_theme' => isset($row['attributes']['variation_theme'][0]['name'])
                            ? [$row['attributes']['variation_theme'][0]['name']]
                            : null,
                        'attributes' => $attrs ?: ($row['attributes'] ?? null),
                        'main_image_url' => $row['images'][0]['link'] ?? null,
                        'default_cost' => $row['cost'] ?? null,
                        'default_price' => $row['listPrice']['amount'] ?? null,
                        'default_currency' => $row['listPrice']['currencyCode'] ?? 'USD',
                        'status' => $role === 'parent' ? 'active' : 'active',
                    ]
                );

                if (! empty($row['asin'])) {
                    $byAsin[$row['asin']] = $product;
                }

                foreach ($row['images'] ?? [] as $i => $img) {
                    ProductImage::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'url' => $img['link'],
                        ],
                        [
                            'organization_id' => $orgId,
                            'variant' => $img['variant'] ?? null,
                            'position' => $i,
                            'width' => $img['width'] ?? null,
                            'height' => $img['height'] ?? null,
                            'is_main' => ($img['variant'] ?? '') === 'MAIN' || $i === 0,
                        ]
                    );
                }
            }

            $job->markSucceeded([
                'products' => $ordered->count(),
                'categories' => count($payload['categories'] ?? []),
            ]);
        } catch (\Throwable $e) {
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
                $product = Product::query()
                    ->where('organization_id', $account->organization_id)
                    ->where('sku', $row['sellerSku'])
                    ->first();

                $listing = Listing::query()
                    ->where('amazon_account_id', $account->id)
                    ->where('seller_sku', $row['sellerSku'])
                    ->first();

                $inv = Inventory::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'sku' => $row['sellerSku'],
                        'fulfillment_channel' => $row['fulfillmentChannel'] ?? 'FBA',
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'marketplace_id' => $marketplace?->id,
                        'listing_id' => $listing?->id,
                        'product_id' => $product?->id,
                        'asin' => $row['asin'] ?? null,
                        'quantity_available' => (int) ($row['totalQuantity'] ?? 0),
                        'quantity_inbound' => (int) ($row['inboundQuantity'] ?? 0),
                        'quantity_reserved' => (int) ($row['reservedQuantity'] ?? 0),
                        'quantity_unfulfillable' => (int) ($row['unfulfillableQuantity'] ?? 0),
                        'warehouse_location' => $row['fnSku'] ?? null,
                        'synced_at' => now(),
                    ]
                );

                if ($listing) {
                    $listing->forceFill(['quantity' => (int) $inv->quantity_available])->save();
                }
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
                $product = Product::query()
                    ->where('organization_id', $account->organization_id)
                    ->where('sku', $row['sku'])
                    ->first();

                $status = $row['status'] ?? 'inactive';
                if (in_array(strtolower((string) $status), ['buyable', 'active'], true)) {
                    $status = 'active';
                } elseif (strtolower((string) $status) === 'suppressed') {
                    $status = 'suppressed';
                } else {
                    $status = 'inactive';
                }

                Listing::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'marketplace_id' => $marketplace->id,
                        'seller_sku' => $row['sku'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'product_id' => $product?->id,
                        'asin' => $row['asin'] ?? null,
                        'title' => $row['title'] ?? null,
                        'price' => $row['price'] ?? $product?->default_price,
                        'currency' => $row['currency'] ?? 'USD',
                        'fulfillment_channel' => $row['fulfillmentChannel'] ?? 'FBA',
                        'status' => $status,
                        'listing_status' => $row['listing_status'] ?? strtoupper($status),
                        'suppress_reason' => $row['suppress_reason'] ?? null,
                        'status_flags' => $row['status_flags'] ?? null,
                        'main_image_url' => $row['main_image_url'] ?? $product?->main_image_url,
                        'amazon_product_type' => $row['amazon_product_type'] ?? $product?->amazon_product_type,
                        'browse_node_id' => $row['browse_node_id'] ?? $product?->browse_node_id,
                        'attributes' => $row['attributes'] ?? $product?->attributes,
                        'open_date' => $row['open_date'] ?? null,
                        'quantity' => $product
                            ? (int) Inventory::query()
                                ->where('amazon_account_id', $account->id)
                                ->where('sku', $row['sku'])
                                ->sum('quantity_available')
                            : null,
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

    public function syncInboundShipments(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'fba_inbound');
        $job->markRunning();

        try {
            $payload = $this->client->getInboundShipments($account);
            $warehouse = Warehouse::query()
                ->where('organization_id', $account->organization_id)
                ->where('type', 'fba')
                ->first()
                ?? Warehouse::query()->where('organization_id', $account->organization_id)->first();

            $count = 0;
            foreach ($payload['ShipmentData'] ?? [] as $row) {
                $shipment = Shipment::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'external_shipment_id' => $row['ShipmentId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'warehouse_id' => $warehouse?->id,
                        'shipment_type' => 'fba_inbound',
                        'reference' => $row['ShipmentName'] ?? null,
                        'status' => $row['ShipmentStatus'] ?? 'WORKING',
                        'carrier' => $row['Carrier'] ?? null,
                        'tracking_number' => $row['TrackingId'] ?? null,
                        'destination_fulfillment_center' => $row['DestinationFulfillmentCenterId'] ?? null,
                        'shipped_at' => $row['ShipDate'] ?? null,
                        'expected_arrival' => $row['EstimatedArrival'] ?? null,
                        'delivered_at' => ($row['ShipmentStatus'] ?? '') === 'CLOSED' ? ($row['EstimatedArrival'] ?? now()) : null,
                        'raw' => $row,
                    ]
                );

                foreach ($row['ItemData'] ?? [] as $item) {
                    ShipmentItem::query()->updateOrCreate(
                        [
                            'shipment_id' => $shipment->id,
                            'sku' => $item['SellerSKU'],
                        ],
                        [
                            'asin' => $item['ASIN'] ?? null,
                            'fnsku' => $item['FulfillmentNetworkSKU'] ?? null,
                            'quantity' => $item['QuantityShipped'] ?? 0,
                            'quantity_received' => $item['QuantityReceived'] ?? 0,
                        ]
                    );
                }
                $count++;
            }

            $job->markSucceeded(['shipments' => $count]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncMessaging(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'messaging');
        $job->markRunning();

        try {
            $messages = $this->client->getBuyerMessages($account);
            $cases = $this->client->getSupportCases($account);
            $msgCount = 0;
            $caseCount = 0;

            foreach ($messages['messages'] ?? [] as $row) {
                $orderId = null;
                if (! empty($row['amazonOrderId'])) {
                    $orderId = Order::query()->where('amazon_order_id', $row['amazonOrderId'])->value('id');
                }

                BuyerMessage::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'external_message_id' => $row['messageId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'order_id' => $orderId,
                        'amazon_order_id' => $row['amazonOrderId'] ?? null,
                        'subject' => $row['subject'] ?? null,
                        'body' => $row['body'] ?? null,
                        'direction' => $row['direction'] ?? 'inbound',
                        'status' => $row['status'] ?? 'unread',
                        'sent_at' => $row['sentAt'] ?? null,
                    ]
                );
                $msgCount++;
            }

            foreach ($cases['cases'] ?? [] as $row) {
                $orderId = null;
                if (! empty($row['amazonOrderId'])) {
                    $orderId = Order::query()->where('amazon_order_id', $row['amazonOrderId'])->value('id');
                }

                SupportCase::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'case_id' => $row['caseId'],
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'order_id' => $orderId,
                        'case_type' => $row['caseType'] ?? null,
                        'status' => $row['status'] ?? 'open',
                        'subject' => $row['subject'] ?? null,
                        'description' => $row['description'] ?? null,
                        'opened_at' => $row['openedAt'] ?? null,
                        'closed_at' => $row['closedAt'] ?? null,
                    ]
                );
                $caseCount++;
            }

            $job->markSucceeded(['messages' => $msgCount, 'cases' => $caseCount]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncAdPerformance(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'ad_metrics');
        $job->markRunning();

        try {
            $payload = $this->client->getAdPerformance($account);
            $currency = $payload['currency'] ?? 'USD';
            $count = 0;

            foreach ($payload['rows'] ?? [] as $row) {
                $campaignId = (string) ($row['campaignId'] ?? '');
                $campaign = AdCampaign::query()
                    ->where('organization_id', $account->organization_id)
                    ->where('external_campaign_id', $campaignId)
                    ->first();

                $cost = (float) ($row['cost'] ?? 0);
                $sales = (float) ($row['attributedSales14d'] ?? $row['sales'] ?? 0);
                $date = Carbon::parse($row['date'])->toDateString();

                AdPerformanceMetric::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'external_campaign_id' => $campaignId,
                        'report_date' => $date,
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'ad_campaign_id' => $campaign?->id,
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'clicks' => (int) ($row['clicks'] ?? 0),
                        'cost' => $cost,
                        'sales' => $sales,
                        'orders' => (int) ($row['attributedConversions14d'] ?? $row['orders'] ?? 0),
                        'acos' => $sales > 0 ? round($cost / $sales, 4) : null,
                        'roas' => $cost > 0 ? round($sales / $cost, 4) : null,
                        'currency' => $currency,
                    ]
                );
                $count++;
            }

            $job->markSucceeded(['rows' => $count]);
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

    public function syncProductQualifications(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'product_qualifications');
        $job->markRunning();

        try {
            $payload = $this->client->getProductQualifications($account);
            $count = 0;

            foreach ($payload['qualifications'] ?? [] as $row) {
                $marketplace = null;
                if (! empty($row['marketplaceId'])) {
                    $marketplace = Marketplace::query()->where('code', $row['marketplaceId'])->first();
                }

                $product = null;
                if (! empty($row['sku'])) {
                    $product = Product::query()
                        ->where('organization_id', $account->organization_id)
                        ->where('sku', $row['sku'])
                        ->first();
                }
                if (! $product && ! empty($row['asin'])) {
                    $product = Product::query()
                        ->where('organization_id', $account->organization_id)
                        ->where('asin', $row['asin'])
                        ->first();
                }

                $externalId = $row['externalId'] ?? ('pq-'.md5(json_encode($row)));

                $record = ProductQualification::query()->updateOrCreate(
                    [
                        'organization_id' => $account->organization_id,
                        'external_id' => $externalId,
                    ],
                    [
                        'product_id' => $product?->id,
                        'marketplace_id' => $marketplace?->id,
                        'asin' => $row['asin'] ?? $product?->asin,
                        'sku' => $row['sku'] ?? $product?->sku,
                        'qualification_type' => $row['qualificationType'] ?? 'other',
                        'name' => $row['name'] ?? 'Untitled qualification',
                        'document_number' => $row['documentNumber'] ?? null,
                        'issuer' => $row['issuer'] ?? null,
                        'status' => $row['status'] ?? 'valid',
                        'issued_at' => isset($row['issuedAt']) ? Carbon::parse($row['issuedAt'])->toDateString() : null,
                        'expires_at' => isset($row['expiresAt']) ? Carbon::parse($row['expiresAt'])->toDateString() : null,
                        'file_url' => $row['fileUrl'] ?? null,
                        'notes' => $row['notes'] ?? null,
                        'raw' => $row,
                    ]
                );
                $record->refreshExpiryStatus();
                $count++;
            }

            $job->markSucceeded(['product_qualifications' => $count]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncStoreQualifications(AmazonAccount $account, ?SyncJob $job = null): SyncJob
    {
        $job ??= $this->startJob($account, 'store_qualifications');
        $job->markRunning();

        try {
            $payload = $this->client->getStoreQualifications($account);
            $count = 0;

            foreach ($payload['qualifications'] ?? [] as $row) {
                $marketplace = null;
                if (! empty($row['marketplaceId'])) {
                    $marketplace = Marketplace::query()->where('code', $row['marketplaceId'])->first();
                }

                $externalId = $row['externalId'] ?? ('sq-'.md5(json_encode($row)));

                $record = StoreQualification::query()->updateOrCreate(
                    [
                        'amazon_account_id' => $account->id,
                        'external_id' => $externalId,
                    ],
                    [
                        'organization_id' => $account->organization_id,
                        'marketplace_id' => $marketplace?->id,
                        'qualification_type' => $row['qualificationType'] ?? 'other',
                        'name' => $row['name'] ?? 'Untitled store qualification',
                        'brand_name' => $row['brandName'] ?? null,
                        'category_name' => $row['categoryName'] ?? null,
                        'document_number' => $row['documentNumber'] ?? null,
                        'status' => $row['status'] ?? 'approved',
                        'issued_at' => isset($row['issuedAt']) ? Carbon::parse($row['issuedAt'])->toDateString() : null,
                        'expires_at' => isset($row['expiresAt']) ? Carbon::parse($row['expiresAt'])->toDateString() : null,
                        'file_url' => $row['fileUrl'] ?? null,
                        'notes' => $row['notes'] ?? null,
                        'raw' => $row,
                    ]
                );
                $record->refreshExpiryStatus();
                $count++;
            }

            $job->markSucceeded(['store_qualifications' => $count]);
        } catch (\Throwable $e) {
            $job->markFailed($e->getMessage());
        }

        return $job->fresh();
    }

    public function syncAll(AmazonAccount $account): array
    {
        $results = DB::transaction(function () use ($account) {
            // catalog 先于 listings/inventory，便于关联 product_id / quantity
            return [
                'catalog' => $this->syncCatalog($account),
                'listings' => $this->syncListings($account),
                'inventory' => $this->syncInventory($account),
                'orders' => $this->syncOrders($account),
                'finance' => $this->syncFinance($account),
                'ads' => $this->syncAdvertising($account),
                'ad_metrics' => $this->syncAdPerformance($account),
                'returns' => $this->syncReturns($account),
                'fba_inbound' => $this->syncInboundShipments($account),
                'messaging' => $this->syncMessaging($account),
                'product_qualifications' => $this->syncProductQualifications($account),
                'store_qualifications' => $this->syncStoreQualifications($account),
            ];
        });

        $account->forceFill(['last_synced_at' => now()])->save();

        return $results;
    }

    /** 任意 scope 成功后刷新账号上次同步时间 */
    public function touchSyncedAt(AmazonAccount $account): void
    {
        $account->forceFill(['last_synced_at' => now()])->save();
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
