<?php

namespace App\Services\Amazon;

use App\Contracts\AmazonSpApiClient;
use App\Models\AmazonAccount;
use Illuminate\Support\Str;

/**
 * 基于 JSON Fixture 的 Amazon SP-API 模拟客户端（无真实网络调用）
 */
class FixtureAmazonSpApiClient implements AmazonSpApiClient
{
    public function __construct(
        protected FixtureLoader $fixtures
    ) {}

    public function validateCredentials(AmazonAccount $account): bool
    {
        return $account->exists;
    }

    public function getOrders(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('orders/getOrders.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => [
                'driver' => 'fixture',
                'fixture' => 'orders/getOrders.json',
                'account_id' => $account->id,
            ],
        ]);
    }

    public function getOrderItems(AmazonAccount $account, string $amazonOrderId): array
    {
        $data = $this->fixtures->load('orders/getOrderItems.json', $account);
        $map = $data['payload'] ?? $data;
        $items = $map[$amazonOrderId] ?? ['OrderItems' => []];

        return array_merge($items, [
            '_meta' => [
                'driver' => 'fixture',
                'fixture' => 'orders/getOrderItems.json',
                'amazon_order_id' => $amazonOrderId,
            ],
        ]);
    }

    public function getInventory(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('inventory/getInventorySummaries.json', $account);
        $payload = $data['payload'] ?? $data;

        // 归一化：把 inventoryDetails 展开成同步服务易用的扁平字段
        $summaries = [];
        foreach ($payload['inventorySummaries'] ?? [] as $row) {
            $details = $row['inventoryDetails'] ?? [];
            $summaries[] = array_merge($row, [
                'totalQuantity' => $details['fulfillableQuantity'] ?? $row['totalQuantity'] ?? 0,
                'inboundQuantity' => ($details['inboundWorkingQuantity'] ?? 0)
                    + ($details['inboundShippedQuantity'] ?? 0)
                    + ($details['inboundReceivingQuantity'] ?? 0),
                'reservedQuantity' => $details['reservedQuantity']['totalReservedQuantity']
                    ?? $details['reservedQuantity']
                    ?? 0,
                'unfulfillableQuantity' => $details['unfulfillableQuantity']['totalUnfulfillableQuantity']
                    ?? $details['unfulfillableQuantity']
                    ?? 0,
            ]);
        }

        return [
            'granularity' => $payload['granularity'] ?? null,
            'inventorySummaries' => $summaries,
            '_meta' => ['driver' => 'fixture', 'fixture' => 'inventory/getInventorySummaries.json'],
        ];
    }

    public function getListings(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('listings/searchListingsItems.json', $account);
        $payload = $data['payload'] ?? $data;

        $items = [];
        foreach ($payload['items'] ?? [] as $row) {
            $summary = $row['summaries'][0] ?? [];
            $offer = $row['offers'][0] ?? [];
            $statuses = $summary['status'] ?? [];
            $channelCode = $offer['fulfillmentChannelCode'] ?? '';

            $primaryStatus = $row['listingStatus']
                ?? (in_array('SUPPRESSED', $statuses, true) ? 'suppressed'
                    : (in_array('BUYABLE', $statuses, true) ? 'active' : 'inactive'));

            $items[] = [
                'sku' => $row['sku'] ?? null,
                'asin' => $summary['asin'] ?? null,
                'title' => $summary['itemName'] ?? null,
                'price' => isset($offer['price']['amount']) ? (float) $offer['price']['amount'] : null,
                'currency' => $offer['price']['currencyCode'] ?? 'USD',
                'status' => $primaryStatus,
                'listing_status' => strtoupper((string) ($statuses[0] ?? $primaryStatus)),
                'status_flags' => $statuses,
                'suppress_reason' => $row['suppressReason'] ?? null,
                'main_image_url' => $summary['mainImage']['link'] ?? null,
                'amazon_product_type' => $summary['productType'] ?? null,
                'browse_node_id' => $row['browseNodeId'] ?? null,
                'attributes' => $row['attributes'] ?? null,
                'open_date' => $summary['createdDate'] ?? null,
                'fulfillmentChannel' => $row['fulfillmentChannel']
                    ?? (str_starts_with((string) $channelCode, 'AMAZON') ? 'FBA' : 'FBM'),
                'marketplaceId' => $summary['marketplaceId'] ?? null,
                'raw' => $row,
            ];
        }

        return [
            'numberOfResults' => $payload['numberOfResults'] ?? count($items),
            'items' => $items,
            '_meta' => ['driver' => 'fixture', 'fixture' => 'listings/searchListingsItems.json'],
        ];
    }

    public function createReport(AmazonAccount $account, string $reportType, array $options = []): array
    {
        $data = $this->fixtures->load('reports/createReport.json', $account, [
            '{{report_type}}' => $reportType,
        ]);
        $payload = $data['payload'] ?? $data;

        // 确保每次创建有唯一 reportId
        if (! isset($payload['reportId']) || str_contains((string) $payload['reportId'], 'fixture-report-')) {
            $payload['reportId'] = 'fixture-report-'.Str::uuid()->toString();
        }

        return array_merge($payload, [
            'reportType' => $reportType,
            'processingStatus' => 'IN_QUEUE',
            'marketplaceIds' => $options['marketplaceIds'] ?? ['ATVPDKIKX0DER'],
            '_meta' => ['driver' => 'fixture', 'fixture' => 'reports/createReport.json'],
        ]);
    }

    public function getReport(AmazonAccount $account, string $reportId): array
    {
        $data = $this->fixtures->load('reports/getReport.json', $account, [
            '{{report_id}}' => $reportId,
            '{{report_type}}' => 'GET_FLAT_FILE_ALL_ORDERS_DATA_BY_ORDER_DATE_GENERAL',
        ]);
        $payload = $data['payload'] ?? $data;
        $payload['reportId'] = $reportId;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'reports/getReport.json'],
        ]);
    }

    public function getFinancialEvents(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('finance/listFinancialEvents.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'finance/listFinancialEvents.json'],
        ]);
    }

    public function getAdCampaigns(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('advertising/spCampaigns.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'advertising/spCampaigns.json'],
        ]);
    }

    public function getReturns(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('returns/listReturns.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'returns/listReturns.json'],
        ]);
    }

    public function getCatalogItems(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('catalog/getCatalogItem.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'catalog/getCatalogItem.json'],
        ]);
    }

    public function getInboundShipments(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('fba/inboundShipments.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'fba/inboundShipments.json'],
        ]);
    }

    public function getBuyerMessages(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('messaging/listMessages.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'messaging/listMessages.json'],
        ]);
    }

    public function getSupportCases(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('messaging/listCases.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'messaging/listCases.json'],
        ]);
    }

    public function getAdPerformance(AmazonAccount $account, array $params = []): array
    {
        $data = $this->fixtures->load('advertising/spCampaignMetrics.json', $account);
        $payload = $data['payload'] ?? $data;

        return array_merge($payload, [
            '_meta' => ['driver' => 'fixture', 'fixture' => 'advertising/spCampaignMetrics.json'],
        ]);
    }
}
