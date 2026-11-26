# 跨境 ERP（Amazon SP-API 导向脚手架）

基于 **Laravel 12 + MySQL** 的亚马逊跨境卖家 ERP 起步工程：多租户组织、多店铺、中文后台、JSON API。

**默认不调用真实 Amazon API。** `AMAZON_DRIVER=fixture` 读取 SP-API 形态的 JSON fixtures 模拟同步并落库。

## 快速开始

```bash
git clone https://github.com/vf2ngnrn7r-sketch/cross-border-erp.git
cd cross-border-erp

composer install
cp .env.example .env
php artisan key:generate

# 编辑 .env 中的 MySQL：
# DB_DATABASE=cross_border_erp
# DB_USERNAME=erp
# DB_PASSWORD=erp_secret

php artisan migrate --seed
npm install && npm run build
php artisan serve
```

打开 http://127.0.0.1:8000 ，演示登录：

| | |
|--|--|
| 邮箱 | `admin@example.com` |
| 密码 | `password` |

Seed 会用 Fixture 驱动跑一次全量同步，后台即可看到商品/订单/库存/资质等演示数据。

可选：`php artisan queue:work`（后台「同步」按钮走队列）、`php artisan schedule:work`（定时同步）。

## Amazon 驱动（默认 fixture）

```env
AMAZON_DRIVER=fixture
# AMAZON_FIXTURES_PATH=   # 空则用 resources/amazon/fixtures
```

| 驱动 | 行为 |
|------|------|
| `fixture`（默认） | 读 `resources/amazon/fixtures/**/*.json`，无外网 |
| `stub` | 进程内少量假数据 |
| `http` | 未实现；安全回退 fixture，**不会**访问 Amazon |

映射与占位符见 `resources/amazon/fixtures/README.md`（含 `{{now_add_days:N}}` 等）。

## 同步命令

```bash
php artisan amazon:fixtures:list
php artisan amazon:sync                          # 全量
php artisan amazon:sync --scope=catalog
php artisan amazon:sync --scope=listings
php artisan amazon:sync --scope=inventory
php artisan amazon:sync --scope=orders
php artisan amazon:sync --scope=finance
php artisan amazon:sync --scope=ads
php artisan amazon:sync --scope=ad_metrics
php artisan amazon:sync --scope=returns
php artisan amazon:sync --scope=fba_inbound
php artisan amazon:sync --scope=messaging
php artisan amazon:sync --scope=product_qualifications
php artisan amazon:sync --scope=store_qualifications
php artisan amazon:sync --account=1 --scope=orders --queue
php artisan amazon:report --account=1
```

后台：亚马逊账号详情页可按 scope 投递队列同步。定时任务见 `routes/console.php`。

## 模块地图（Admin / API）

| 模块 | Admin | API (`/api/v1`，session 认证) |
|------|-------|------------------------------|
| 总览 / 报表 | `/admin`、`/admin/reports` | `/reports/summary` |
| 亚马逊账号 | `/admin/amazon-accounts` | `/amazon-accounts` |
| 商品目录 | `/admin/products`（CRUD） | `/products`、`/products/{id}`、`/variants`、`/categories` |
| 商品资质 | `/admin/product-qualifications`（CRUD） | `/product-qualifications` |
| 店铺资质 | `/admin/store-qualifications`（CRUD） | `/store-qualifications` |
| Listing | `/admin/listings` | `/listings` |
| 库存 | `/admin/inventories` | `/inventories` |
| 采购 | `/admin/purchasing` | `/suppliers`、`/purchase-orders` |
| 仓配 | `/admin/warehouse` | `/warehouses`、`/shipments` |
| 订单 | `/admin/orders` | `/orders` |
| 财务 | `/admin/finance` | `/settlements` |
| 广告 | `/admin/advertising` | `/campaigns`、`/ad-metrics` |
| 消息 | `/admin/messaging` | `/messages`、`/cases` |
| 退货 | `/admin/returns` | `/returns` |
| 同步任务 | `/admin/sync-jobs` | `/sync-jobs` |
| 健康检查 | — | `/health`（无需登录） |

## 功能摘要

- 认证 / 多租户：`organizations` + `organization_user`
- 商品：父子变体、Browse Node、图片、Listing 状态、库存挂钩
- 资质：商品证书/类目准入；店铺 Brand Registry/类目批准；到期预警
- 采购 / 仓配：内部 ERP（供应商、PO、FBA 入库 fixture、FBM 出库）
- 财务 / 广告 / 退货 / 消息：Fixture 同步落库
- 凭证字段加密占位；仓库内无真实 Amazon 密钥

## 目录要点

```
app/Contracts/AmazonSpApiClient.php
app/Services/Amazon/{FixtureLoader,FixtureAmazonSpApiClient,StubAmazonSpApiClient,AmazonSyncService}.php
resources/amazon/fixtures/
config/amazon.php
```

## 已实现 vs 未实现

**已实现：** 迁移与模型、组织隔离、中文后台、API、种子、Fixture 同步落库、队列/调度骨架。

**未实现：** 真实 SP-API / Advertising HTTP、LWA、SigV4、报表文档下载解析、生产级 RBAC。

## 环境要求

PHP 8.2+、Composer 2、MySQL 8+、Node.js 18+（Vite）。

## 许可证

MIT
