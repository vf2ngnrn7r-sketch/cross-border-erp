# Amazon API JSON Fixtures

本地默认通过 `AMAZON_DRIVER=fixture` 加载本目录下的 JSON，**不发起真实 Amazon 网络请求**。

## 文件映射

| 文件 | 客户端方法 | 参考 API 形态 |
|------|------------|---------------|
| `orders/getOrders.json` | `getOrders` | Orders API `getOrders` |
| `orders/getOrderItems.json` | （同步服务按订单号查找） | Orders API `getOrderItems` |
| `inventory/getInventorySummaries.json` | `getInventory` | FBA Inventory `getInventorySummaries` |
| `listings/searchListingsItems.json` | `getListings` | Listings Items API |
| `catalog/getCatalogItem.json` | （扩展预留） | Catalog Items API |
| `finance/listFinancialEvents.json` | `getFinancialEvents` | Finances API |
| `advertising/spCampaigns.json` | `getAdCampaigns` | Advertising SP campaigns |
| `returns/listReturns.json` | `getReturns` | Returns / FBA customer returns |
| `reports/createReport.json` | `createReport` | Reports API |
| `reports/getReport.json` | `getReport` | Reports API |

## 添加 / 修改 Fixture

1. 按上表路径新建或编辑 JSON（保持 SP-API 字段命名，便于日后换真实驱动）。
2. 可选：按账号覆盖  
   `resources/amazon/fixtures/accounts/{seller_id}/orders/getOrders.json`  
   若存在则优先于默认文件。
3. 运行 `php artisan amazon:sync` 或后台「同步」，数据会经 `AmazonSyncService` 落库。
4. 可用 `php artisan amazon:fixtures:list` 查看当前加载路径。

占位符：部分字段支持 `{{now}}`、`{{now_sub_hours:6}}`、`{{uuid}}`、`{{account_id}}`，由 Fixture 客户端在加载时替换。
