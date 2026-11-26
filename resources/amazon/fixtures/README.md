# Amazon API JSON Fixtures

本地默认通过 `AMAZON_DRIVER=fixture` 加载本目录下的 JSON，**不发起真实 Amazon 网络请求**。

## 文件映射

| 文件 | 客户端方法 | 参考 API 形态 |
|------|------------|---------------|
| `orders/getOrders.json` | `getOrders` | Orders API `getOrders` |
| `orders/getOrderItems.json` | `getOrderItems` | Orders API `getOrderItems` |
| `inventory/getInventorySummaries.json` | `getInventory` | FBA Inventory |
| `listings/searchListingsItems.json` | `getListings` | Listings Items API |
| `catalog/getCatalogItem.json` | `getCatalogItems` | Catalog Items API |
| `finance/listFinancialEvents.json` | `getFinancialEvents` | Finances API |
| `advertising/spCampaigns.json` | `getAdCampaigns` | Advertising SP campaigns |
| `advertising/spCampaignMetrics.json` | `getAdPerformance` | Advertising reporting |
| `returns/listReturns.json` | `getReturns` | Returns + A-Z + feedback |
| `fba/inboundShipments.json` | `getInboundShipments` | Fulfillment Inbound |
| `messaging/listMessages.json` | `getBuyerMessages` | Buyer-Seller Messaging |
| `messaging/listCases.json` | `getSupportCases` | Case management stubs |
| `reports/createReport.json` | `createReport` | Reports API |
| `reports/getReport.json` | `getReport` | Reports API |

采购 / 本地仓等**内部 ERP**数据不走 Amazon fixture，由 Seeder 写入。

## 添加 / 修改 Fixture

1. 按上表路径新建或编辑 JSON（保持 SP-API 字段命名）。
2. 可选账号覆盖：`accounts/{seller_id}/...` 同名相对路径优先。
3. `php artisan amazon:sync` 或后台同步 → `AmazonSyncService` 落库。
4. `php artisan amazon:fixtures:list` 查看文件。

占位符：`{{now}}`、`{{now_sub_hours:N}}`、`{{now_sub_days:N}}`、`{{uuid}}`、`{{account_id}}`、`{{seller_id}}`。
