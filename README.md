# 跨境 ERP（Amazon SP-API 导向脚手架）

基于 **Laravel 12 + MySQL** 的亚马逊跨境卖家 ERP 起步工程：多租户组织、多店铺、商品/库存/订单/财务/广告/退货与同步任务骨架。

**默认不调用真实 Amazon API。** 本地通过 `AMAZON_DRIVER=fixture` 读取 SP-API 形态的 JSON fixtures 模拟同步。

## 功能模块

| 模块 | 说明 |
|------|------|
| 认证 / 多租户 | Laravel Breeze；`organizations` + `organization_user`；用户 `current_organization_id` |
| 亚马逊账号 | 店铺、站点（marketplace）、SP-API 凭证绑定（加密字段 + 占位符） |
| 商品 / Listing | 商品主数据与站点 Listing CRUD / 列表 |
| 库存 | FBA / FBM 数量字段与同步落库 |
| 订单 | 订单、订单行、履约状态 |
| 财务 | 结算单、费用/财务事件（经 Fixture 同步） |
| 广告 | 广告活动 / 广告组（Fixture → 落库） |
| 退货 / A-Z / 反馈 | 退货、A-to-Z 索赔、买家反馈 |
| 报表 / 同步 | `SyncJob`、`ReportRequest`、队列 Job、`amazon:sync` / `amazon:report`、定时任务 |
| 管理 UI | `/admin/*` 中文后台 |
| JSON API | `/api/v1/*`（session 认证）+ `/api/v1/health` |

## 环境要求

- PHP 8.2+（扩展：mbstring、openssl、pdo_mysql、tokenizer、xml、ctype、json、bcmath）
- Composer 2
- MySQL 8+
- Node.js 18+（前端资源 Vite）

## 快速开始

```bash
git clone https://github.com/vf2ngnrn7r-sketch/cross-border-erp.git
cd cross-border-erp

composer install
cp .env.example .env
php artisan key:generate

# 编辑 .env 中的 MySQL 连接，例如：
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=cross_border_erp
# DB_USERNAME=erp
# DB_PASSWORD=erp_secret

php artisan migrate --seed
npm install && npm run build

php artisan serve
```

浏览器打开 `http://127.0.0.1:8000`，演示账号：

- 邮箱：`admin@example.com`
- 密码：`password`

Seed 会通过 Fixture 驱动执行一次全量同步，后台即可看到订单/库存/财务等数据。

## Amazon 驱动（模拟优先）

```env
# fixture（默认）| stub | http（未实现，会安全回退 fixture）
AMAZON_DRIVER=fixture
# 可选：自定义 fixture 根目录（默认 resources/amazon/fixtures）
AMAZON_FIXTURES_PATH=

# 以下仅为占位，切勿填入真实密钥提交到仓库
AMAZON_SP_API_CLIENT_ID=
AMAZON_SP_API_CLIENT_SECRET=
AMAZON_SP_API_REFRESH_TOKEN=
AMAZON_SP_API_ACCESS_KEY_ID=
AMAZON_SP_API_SECRET_ACCESS_KEY=
AMAZON_SP_API_REGION=na
AMAZON_SP_API_ENDPOINT=https://sellingpartnerapi-na.amazon.com
AMAZON_LWA_ENDPOINT=https://api.amazon.com/auth/o2/token
AMAZON_SP_API_STUB=true
```

| 驱动 | 行为 |
|------|------|
| `fixture` | 读取 `resources/amazon/fixtures/**/*.json`（推荐） |
| `stub` | 进程内生成少量假数据（无文件） |
| `http` | 预留；当前回退到 fixture，**不会**访问 Amazon |

绑定逻辑见 `AppServiceProvider` → `AmazonSpApiClient`。

### Fixture 说明

- 目录说明与文件映射：`resources/amazon/fixtures/README.md`
- 覆盖范围：orders、order items、inventory、listings、catalog、finance、advertising、returns（含 A-Z/反馈）、reports
- 占位符：`{{now}}`、`{{now_sub_hours:N}}`、`{{uuid}}`、`{{account_id}}` 等
- 按店铺覆盖：`resources/amazon/fixtures/accounts/{seller_id}/...`

```bash
# 列出当前 fixture
php artisan amazon:fixtures:list

# 从 fixture 同步到数据库（供后台/API 展示）
php artisan amazon:sync
php artisan amazon:sync --scope=orders
php artisan amazon:sync --scope=ads
```

### 如何新增 Fixture

1. 在 `resources/amazon/fixtures/<模块>/` 添加或修改 JSON，尽量保持 SP-API 字段名。
2. 如需账号差异，放到 `accounts/{seller_id}/` 同名相对路径。
3. 若新增接口方法：扩展 `AmazonSpApiClient` + `FixtureAmazonSpApiClient`，并在 `AmazonSyncService` 落库。
4. 运行 `php artisan amazon:sync --scope=...` 验证。

## 常用命令

```bash
php artisan amazon:sync
php artisan amazon:sync --scope=orders --queue
php artisan amazon:sync --account=1 --scope=inventory
php artisan amazon:report --account=1
php artisan amazon:fixtures:list

php artisan queue:work
php artisan schedule:work
```

定时任务见 `routes/console.php`。

## 目录要点

```
app/Contracts/AmazonSpApiClient.php
app/Services/Amazon/FixtureLoader.php
app/Services/Amazon/FixtureAmazonSpApiClient.php
app/Services/Amazon/StubAmazonSpApiClient.php
app/Services/Amazon/AmazonSyncService.php
resources/amazon/fixtures/            # SP-API 形态 JSON
config/amazon.php
```

## 已实现 vs 模拟

**已实现：** 迁移与模型、组织隔离、后台 UI、API、种子、**Fixture 驱动同步落库**、队列/调度骨架。

**模拟 / 未实现：** 真实 SP-API / Advertising HTTP、LWA 换票、SigV4、报表文档下载解析、生产 RBAC。

## 许可证

MIT
