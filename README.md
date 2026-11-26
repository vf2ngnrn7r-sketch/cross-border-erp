# 跨境 ERP（Amazon SP-API 导向脚手架）

基于 **Laravel 12 + MySQL** 的亚马逊跨境卖家 ERP 起步工程：多租户组织、多店铺、商品/库存/订单/财务/广告/退货与同步任务骨架。Amazon API 以**可替换接口 + 桩客户端**实现，不包含真实密钥。

## 功能模块

| 模块 | 说明 |
|------|------|
| 认证 / 多租户 | Laravel Breeze；`organizations` + `organization_user`；用户 `current_organization_id` |
| 亚马逊账号 | 店铺、站点（marketplace）、SP-API 凭证绑定（加密字段 + 占位符） |
| 商品 / Listing | 商品主数据与站点 Listing CRUD / 列表 |
| 库存 | FBA / FBM 数量字段与同步落库 |
| 订单 | 订单、订单行、履约状态 |
| 财务 | 结算单、费用/财务事件（同步桩） |
| 广告 | 广告活动 / 广告组（数据模型 + 演示种子） |
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

## Amazon SP-API 配置（占位）

`.env` 中相关变量（**勿提交真实密钥**）：

```env
AMAZON_SP_API_STUB=true
AMAZON_SP_API_CLIENT_ID=
AMAZON_SP_API_CLIENT_SECRET=
AMAZON_SP_API_REFRESH_TOKEN=
AMAZON_SP_API_ACCESS_KEY_ID=
AMAZON_SP_API_SECRET_ACCESS_KEY=
AMAZON_SP_API_REGION=na
AMAZON_SP_API_ENDPOINT=https://sellingpartnerapi-na.amazon.com
AMAZON_LWA_ENDPOINT=https://api.amazon.com/auth/o2/token
```

默认 `AMAZON_SP_API_STUB=true`，绑定 `App\Services\Amazon\StubAmazonSpApiClient`。后续实现真实客户端并在 `AppServiceProvider` 中按配置切换即可。

## 常用命令

```bash
# 同步全部活跃店铺（默认同步执行桩逻辑）
php artisan amazon:sync

# 仅同步订单并投递队列
php artisan amazon:sync --scope=orders --queue

# 指定店铺 + 报表请求（桩）
php artisan amazon:sync --account=1 --scope=inventory
php artisan amazon:report --account=1

# 队列与调度
php artisan queue:work
php artisan schedule:work
```

定时任务见 `routes/console.php`（每小时订单、每日库存/财务/Listing）。

## 目录要点

```
app/Contracts/AmazonSpApiClient.php   # API 契约
app/Services/Amazon/                  # 桩客户端 + 同步编排
app/Models/                           # Eloquent 领域模型
app/Http/Controllers/Admin/           # 后台控制器
app/Http/Controllers/Api/V1/          # JSON API
app/Jobs/SyncAmazonAccountJob.php
app/Console/Commands/                 # amazon:sync / amazon:report
database/migrations/2024_01_01_*      # ERP 表结构
database/seeders/ErpDemoSeeder.php
config/amazon.php
resources/views/admin/                # 中文管理界面
```

## 已实现 vs 桩

**已实现（可本地运行）：** 迁移与模型、组织隔离、后台列表/详情与商品 CRUD、API 列表、种子数据、同步编排落库、队列 Job、Artisan 命令与 Schedule 骨架。

**桩 / 占位：** 真实 SP-API / Advertising HTTP 调用、LWA 换票、AWS SigV4、报表文档下载解析、广告投放管理写操作、生产级权限 RBAC、多币种对账规则。

## 许可证

MIT
