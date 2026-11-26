<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AmazonAccount;
use Illuminate\Support\Str;

/**
 * 进程内简易桩（无 JSON 文件）。推荐本地使用 FixtureAmazonSpApiClient。
 * 保留供 AMAZON_DRIVER=stub 或测试快速切换。
 */
class StubAmazonSpApiClient implements AmazonSpApiClient
{
    public function validateCredentials(AmazonAccount $account): bool
    {
        return $account->exists;
    }

    public function getOrders(AmazonAccount $account, array $params = []): array
    {
        return [
            'Orders' => [
                [
                    'AmazonOrderId' => '902-'.$account->id.'-'.Str::upper(Str::random(7)),
                    'PurchaseDate' => now()->subHours(6)->toIso8601String(),
                    'OrderStatus' => 'Unshipped',
                    'FulfillmentChannel' => 'MFN',
                    'SalesChannel' => 'Amazon.com',
                    'OrderTotal' => ['Amount' => '49.99', 'CurrencyCode' => 'USD'],
                    'IsPrime' => false,
                    'LastUpdateDate' => now()->toIso8601String(),
                ],
            ],
            '_meta' => ['driver' => 'stub', 'account_id' => $account->id],
        ];
    }

    public function getOrderItems(AmazonAccount $account, string $amazonOrderId): array
    {
        return [
            'OrderItems' => [
                [
                    'OrderItemId' => 'stub-item-1',
                    'ASIN' => 'B0STUB0001',
                    'SellerSKU' => 'SKU-DEMO-001',
                    'Title' => 'Demo Wireless Earbuds',
                    'QuantityOrdered' => 1,
                    'QuantityShipped' => 0,
                    'ItemPrice' => ['CurrencyCode' => 'USD', 'Amount' => '49.99'],
                ],
            ],
            '_meta' => ['driver' => 'stub', 'amazon_order_id' => $amazonOrderId],
        ];
    }

    public function getInventory(AmazonAccount $account, array $params = []): array
    {
        return [
            'inventorySummaries' => [
                [
                    'asin' => 'B0STUB0001',
                    'sellerSku' => 'SKU-DEMO-001',
                    'fulfillmentChannel' => 'FBA',
                    'totalQuantity' => 120,
                    'inboundQuantity' => 20,
                    'reservedQuantity' => 5,
                    'unfulfillableQuantity' => 2,
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getListings(AmazonAccount $account, array $params = []): array
    {
        return [
            'items' => [
                [
                    'sku' => 'SKU-DEMO-001',
                    'asin' => 'B0STUB0001',
                    'title' => 'Demo Wireless Earbuds',
                    'price' => 29.99,
                    'currency' => 'USD',
                    'status' => 'BUYABLE',
                    'fulfillmentChannel' => 'FBA',
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function createReport(AmazonAccount $account, string $reportType, array $options = []): array
    {
        return [
            'reportId' => 'stub-report-'.Str::uuid()->toString(),
            'reportType' => $reportType,
            'processingStatus' => 'DONE',
            'marketplaceIds' => $options['marketplaceIds'] ?? [],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getReport(AmazonAccount $account, string $reportId): array
    {
        return [
            'reportId' => $reportId,
            'processingStatus' => 'DONE',
            'reportDocumentId' => 'stub-doc-'.Str::random(12),
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getFinancialEvents(AmazonAccount $account, array $params = []): array
    {
        return [
            'FinancialEvents' => [
                'ShipmentEventList' => [
                    [
                        'AmazonOrderId' => '902-STUB-ORDER',
                        'PostedDate' => now()->subDay()->toIso8601String(),
                        'ShipmentItemList' => [
                            [
                                'SellerSKU' => 'SKU-DEMO-001',
                                'QuantityShipped' => 1,
                                'ItemChargeList' => [
                                    ['ChargeType' => 'Principal', 'ChargeAmount' => ['CurrencyCode' => 'USD', 'CurrencyAmount' => 29.99]],
                                ],
                                'ItemFeeList' => [
                                    ['FeeType' => 'Commission', 'FeeAmount' => ['CurrencyCode' => 'USD', 'CurrencyAmount' => -4.50]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getAdCampaigns(AmazonAccount $account, array $params = []): array
    {
        return [
            'campaigns' => [
                [
                    'campaignId' => 'stub-camp-1001',
                    'name' => 'SP Auto Demo',
                    'campaignType' => 'sponsoredProducts',
                    'targetingType' => 'auto',
                    'state' => 'enabled',
                    'dailyBudget' => 25.00,
                    'adGroups' => [
                        ['adGroupId' => 'stub-ag-1', 'name' => 'Default', 'state' => 'enabled', 'defaultBid' => 0.75],
                    ],
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getReturns(AmazonAccount $account, array $params = []): array
    {
        return [
            'returns' => [
                [
                    'returnId' => 'stub-ret-1',
                    'asin' => 'B0STUB0001',
                    'sellerSku' => 'SKU-DEMO-001',
                    'reason' => 'UNWANTED_ITEM',
                    'status' => 'requested',
                    'quantity' => 1,
                    'returnDate' => now()->subDays(2)->toIso8601String(),
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getInboundShipments(AmazonAccount $account, array $params = []): array
    {
        return [
            'ShipmentData' => [
                [
                    'ShipmentId' => 'FBA-STUB-1',
                    'ShipmentStatus' => 'WORKING',
                    'DestinationFulfillmentCenterId' => 'PHX3',
                    'ItemData' => [
                        ['SellerSKU' => 'SKU-DEMO-001', 'ASIN' => 'B0STUB0001', 'QuantityShipped' => 10, 'QuantityReceived' => 0],
                    ],
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getBuyerMessages(AmazonAccount $account, array $params = []): array
    {
        return [
            'messages' => [
                [
                    'messageId' => 'stub-msg-1',
                    'amazonOrderId' => '902-STUB-ORDER',
                    'subject' => 'Stub buyer question',
                    'body' => 'When will it ship?',
                    'direction' => 'inbound',
                    'status' => 'unread',
                    'sentAt' => now()->subHour()->toIso8601String(),
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getSupportCases(AmazonAccount $account, array $params = []): array
    {
        return [
            'cases' => [
                [
                    'caseId' => 'CASE-STUB-1',
                    'caseType' => 'buyer_message',
                    'status' => 'open',
                    'subject' => 'Stub support case',
                    'openedAt' => now()->subDay()->toIso8601String(),
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getAdPerformance(AmazonAccount $account, array $params = []): array
    {
        return [
            'currency' => 'USD',
            'rows' => [
                [
                    'campaignId' => 'stub-camp-1001',
                    'date' => now()->subDay()->toDateString(),
                    'impressions' => 1000,
                    'clicks' => 40,
                    'cost' => 20.0,
                    'attributedSales14d' => 120.0,
                    'attributedConversions14d' => 3,
                ],
            ],
            '_meta' => ['driver' => 'stub'],
        ];
    }

    public function getCatalogItems(AmazonAccount $account, array $params = []): array
    {
        return [
            'items' => [
                [
                    'asin' => 'B0STUB0001',
                    'role' => 'standalone',
                    'sku' => 'SKU-DEMO-001',
                    'summaries' => [
                        [
                            'marketplaceId' => 'ATVPDKIKX0DER',
                            'brandName' => 'DemoBrand',
                            'itemName' => 'Demo Wireless Earbuds',
                            'browseClassification' => ['displayName' => 'Headphones', 'classificationId' => '172541'],
                        ],
                    ],
                    'productTypes' => [['productType' => 'HEADPHONES']],
                    'images' => [
                        ['variant' => 'MAIN', 'link' => 'https://example.invalid/stub-earbud.jpg', 'height' => 500, 'width' => 500],
                    ],
                    'listPrice' => ['currencyCode' => 'USD', 'amount' => 29.99],
                ],
            ],
            'categories' => [],
            '_meta' => ['driver' => 'stub'],
        ];
    }
}
