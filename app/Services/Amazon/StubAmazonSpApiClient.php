<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AmazonAccount;
use Illuminate\Support\Str;

/**
 * SP-API 桩客户端：不发起真实网络请求，返回可演示的结构化假数据
 */
class StubAmazonSpApiClient implements AmazonSpApiClient
{
    public function validateCredentials(AmazonAccount $account): bool
    {
        // 桩模式：只要账号存在即视为「可校验」
        return $account->exists;
    }

    public function getOrders(AmazonAccount $account, array $params = []): array
    {
        $now = now();

        return [
            'Orders' => [
                [
                    'AmazonOrderId' => '902-'.$account->id.'-'.Str::upper(Str::random(7)),
                    'PurchaseDate' => $now->subHours(6)->toIso8601String(),
                    'OrderStatus' => 'Unshipped',
                    'FulfillmentChannel' => 'MFN',
                    'SalesChannel' => 'Amazon.com',
                    'OrderTotal' => ['Amount' => '49.99', 'CurrencyCode' => 'USD'],
                    'NumberOfItemsShipped' => 0,
                    'NumberOfItemsUnshipped' => 1,
                    'IsPrime' => false,
                    'LastUpdateDate' => $now->toIso8601String(),
                ],
            ],
            '_meta' => ['stub' => true, 'account_id' => $account->id],
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
                [
                    'asin' => 'B0STUB0002',
                    'sellerSku' => 'SKU-DEMO-002',
                    'fulfillmentChannel' => 'FBM',
                    'totalQuantity' => 45,
                    'inboundQuantity' => 0,
                    'reservedQuantity' => 0,
                    'unfulfillableQuantity' => 0,
                ],
            ],
            '_meta' => ['stub' => true],
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
            '_meta' => ['stub' => true],
        ];
    }

    public function createReport(AmazonAccount $account, string $reportType, array $options = []): array
    {
        return [
            'reportId' => 'stub-report-'.Str::uuid()->toString(),
            'reportType' => $reportType,
            'processingStatus' => 'DONE',
            'marketplaceIds' => $options['marketplaceIds'] ?? [],
            '_meta' => ['stub' => true],
        ];
    }

    public function getReport(AmazonAccount $account, string $reportId): array
    {
        return [
            'reportId' => $reportId,
            'processingStatus' => 'DONE',
            'reportDocumentId' => 'stub-doc-'.Str::random(12),
            '_meta' => ['stub' => true],
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
                                    ['FeeType' => 'FBAPerUnitFulfillmentFee', 'FeeAmount' => ['CurrencyCode' => 'USD', 'CurrencyAmount' => -3.22]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '_meta' => ['stub' => true],
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
                ],
            ],
            '_meta' => ['stub' => true],
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
            '_meta' => ['stub' => true],
        ];
    }
}
