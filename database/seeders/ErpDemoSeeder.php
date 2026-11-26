<?php

namespace Database\Seeders;

use App\Models\AdCampaign;
use App\Models\AdGroup;
use App\Models\AmazonAccount;
use App\Models\AmazonCredential;
use App\Models\AToZClaim;
use App\Models\CustomerFeedback;
use App\Models\Fulfillment;
use App\Models\Inventory;
use App\Models\Listing;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\User;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 演示数据种子：组织、用户、站点、店铺、商品、订单等
 * 凭证全部为占位符，非真实 Amazon 密钥
 */
class ErpDemoSeeder extends Seeder
{
    public function run(): void
    {
        $marketplaces = $this->seedMarketplaces();

        $org = Organization::query()->updateOrCreate(
            ['slug' => 'demo-seller'],
            ['name' => '演示跨境卖家', 'status' => 'active']
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理员',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'current_organization_id' => $org->id,
            ]
        );

        if (! $org->users()->where('user_id', $user->id)->exists()) {
            $org->users()->attach($user->id, ['role' => 'owner']);
        }

        $account = AmazonAccount::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'seller_id' => 'A1DEMOSELLER01',
            ],
            [
                'name' => '美国站主店铺',
                'status' => 'active',
            ]
        );

        $account->marketplaces()->sync([
            $marketplaces['US']->id => ['is_primary' => true],
            $marketplaces['CA']->id => ['is_primary' => false],
        ]);

        AmazonCredential::query()->updateOrCreate(
            ['amazon_account_id' => $account->id],
            [
                'lwa_client_id' => 'amzn1.application-oa2-client.PLACEHOLDER',
                'lwa_client_secret' => 'PLACEHOLDER_CLIENT_SECRET',
                'lwa_refresh_token' => 'Atzr|PLACEHOLDER_REFRESH_TOKEN',
                'aws_access_key_id' => 'AKIA_PLACEHOLDER',
                'aws_secret_access_key' => 'PLACEHOLDER_AWS_SECRET',
                'api_region' => 'na',
                'role_arn' => 'arn:aws:iam::123456789012:role/PLACEHOLDER',
                'status' => 'draft',
            ]
        );

        $products = [
            ['sku' => 'SKU-DEMO-001', 'asin' => 'B0STUB0001', 'title' => '演示无线耳机', 'brand' => 'DemoBrand', 'category' => 'Electronics'],
            ['sku' => 'SKU-DEMO-002', 'asin' => 'B0STUB0002', 'title' => '演示桌面支架', 'brand' => 'DemoBrand', 'category' => 'Home'],
            ['sku' => 'SKU-DEMO-003', 'asin' => 'B0STUB0003', 'title' => '演示收纳盒套装', 'brand' => 'DemoBrand', 'category' => 'Home'],
        ];

        foreach ($products as $row) {
            $product = Product::query()->updateOrCreate(
                ['organization_id' => $org->id, 'sku' => $row['sku']],
                [...$row, 'status' => 'active', 'attributes' => ['color' => 'Black']]
            );

            Listing::query()->updateOrCreate(
                [
                    'amazon_account_id' => $account->id,
                    'marketplace_id' => $marketplaces['US']->id,
                    'seller_sku' => $row['sku'],
                ],
                [
                    'organization_id' => $org->id,
                    'product_id' => $product->id,
                    'asin' => $row['asin'],
                    'title' => $row['title'],
                    'price' => fake()->randomFloat(2, 9, 79),
                    'currency' => 'USD',
                    'fulfillment_channel' => $row['sku'] === 'SKU-DEMO-002' ? 'FBM' : 'FBA',
                    'status' => 'active',
                    'synced_at' => now(),
                ]
            );
        }

        Inventory::query()->updateOrCreate(
            ['amazon_account_id' => $account->id, 'sku' => 'SKU-DEMO-001', 'fulfillment_channel' => 'FBA'],
            [
                'organization_id' => $org->id,
                'marketplace_id' => $marketplaces['US']->id,
                'asin' => 'B0STUB0001',
                'quantity_available' => 120,
                'quantity_reserved' => 5,
                'quantity_inbound' => 20,
                'quantity_unfulfillable' => 2,
                'warehouse_location' => 'PHX3',
                'synced_at' => now(),
            ]
        );

        Inventory::query()->updateOrCreate(
            ['amazon_account_id' => $account->id, 'sku' => 'SKU-DEMO-002', 'fulfillment_channel' => 'FBM'],
            [
                'organization_id' => $org->id,
                'marketplace_id' => $marketplaces['US']->id,
                'asin' => 'B0STUB0002',
                'quantity_available' => 45,
                'quantity_reserved' => 0,
                'quantity_inbound' => 0,
                'quantity_unfulfillable' => 0,
                'warehouse_location' => 'CN-SZ-LOCAL',
                'synced_at' => now(),
            ]
        );

        $order = Order::query()->updateOrCreate(
            ['amazon_order_id' => '111-DEMO-0000001'],
            [
                'organization_id' => $org->id,
                'amazon_account_id' => $account->id,
                'marketplace_id' => $marketplaces['US']->id,
                'purchase_date' => now()->subDay(),
                'order_status' => 'Shipped',
                'fulfillment_channel' => 'AFN',
                'sales_channel' => 'Amazon.com',
                'ship_service_level' => 'Standard',
                'order_total' => 29.99,
                'currency' => 'USD',
                'buyer_email' => 'buyer***@marketplace.amazon.com',
                'shipping_address' => [
                    'City' => 'Seattle',
                    'StateOrRegion' => 'WA',
                    'PostalCode' => '98101',
                    'CountryCode' => 'US',
                ],
                'payment_method' => 'Other',
                'is_prime' => true,
                'last_update_date' => now()->subHours(3),
            ]
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'order_item_id' => 'DEMO-ITEM-1'],
            [
                'asin' => 'B0STUB0001',
                'seller_sku' => 'SKU-DEMO-001',
                'title' => '演示无线耳机',
                'quantity_ordered' => 1,
                'quantity_shipped' => 1,
                'item_price' => 29.99,
                'shipping_price' => 0,
                'item_tax' => 2.40,
                'currency' => 'USD',
            ]
        );

        Fulfillment::query()->updateOrCreate(
            ['order_id' => $order->id, 'shipment_id' => 'FBA-DEMO-SHIP-1'],
            [
                'carrier' => 'Amazon Logistics',
                'tracking_number' => 'TBA'.Str::upper(Str::random(10)),
                'status' => 'delivered',
                'shipped_at' => now()->subHours(20),
                'estimated_arrival' => now()->subHours(2),
            ]
        );

        $campaign = AdCampaign::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'external_campaign_id' => 'stub-camp-1001',
            ],
            [
                'amazon_account_id' => $account->id,
                'marketplace_id' => $marketplaces['US']->id,
                'name' => 'SP 自动投放演示',
                'campaign_type' => 'sp',
                'targeting_type' => 'auto',
                'state' => 'enabled',
                'daily_budget' => 25.00,
                'start_date' => now()->subMonth()->toDateString(),
            ]
        );

        AdGroup::query()->updateOrCreate(
            [
                'ad_campaign_id' => $campaign->id,
                'external_ad_group_id' => 'stub-ag-2001',
            ],
            [
                'name' => '默认广告组',
                'state' => 'enabled',
                'default_bid' => 0.75,
            ]
        );

        ProductReturn::query()->updateOrCreate(
            ['amazon_account_id' => $account->id, 'return_id' => 'RET-DEMO-1'],
            [
                'organization_id' => $org->id,
                'order_id' => $order->id,
                'asin' => 'B0STUB0001',
                'seller_sku' => 'SKU-DEMO-001',
                'reason' => 'UNWANTED_ITEM',
                'status' => 'requested',
                'quantity' => 1,
                'return_date' => now()->subDays(2),
            ]
        );

        AToZClaim::query()->updateOrCreate(
            ['amazon_account_id' => $account->id, 'claim_id' => 'AZ-DEMO-1'],
            [
                'organization_id' => $org->id,
                'order_id' => $order->id,
                'reason' => 'Item not received',
                'status' => 'open',
                'opened_at' => now()->subDays(5),
            ]
        );

        CustomerFeedback::query()->updateOrCreate(
            [
                'organization_id' => $org->id,
                'order_id' => $order->id,
            ],
            [
                'amazon_account_id' => $account->id,
                'rating' => 5,
                'comments' => 'Great product, fast shipping!',
                'feedback_date' => now()->subDay(),
            ]
        );

        // 通过桩客户端再跑一轮同步，产生 SyncJob / Settlement 等记录
        app(AmazonSyncService::class)->syncAll($account->fresh());

        $this->command?->info('演示账号: admin@example.com / password');
    }

    /** @return array<string, Marketplace> */
    protected function seedMarketplaces(): array
    {
        $defs = [
            ['code' => 'ATVPDKIKX0DER', 'country_code' => 'US', 'name' => 'Amazon.com', 'currency' => 'USD', 'domain' => 'amazon.com', 'region' => 'na'],
            ['code' => 'A2EUQ1WTGCTBG2', 'country_code' => 'CA', 'name' => 'Amazon.ca', 'currency' => 'CAD', 'domain' => 'amazon.ca', 'region' => 'na'],
            ['code' => 'A1AM78C64UM0Y8', 'country_code' => 'MX', 'name' => 'Amazon.com.mx', 'currency' => 'MXN', 'domain' => 'amazon.com.mx', 'region' => 'na'],
            ['code' => 'A1F83G8C2ARO7P', 'country_code' => 'GB', 'name' => 'Amazon.co.uk', 'currency' => 'GBP', 'domain' => 'amazon.co.uk', 'region' => 'eu'],
            ['code' => 'A1PA6795UKMFR9', 'country_code' => 'DE', 'name' => 'Amazon.de', 'currency' => 'EUR', 'domain' => 'amazon.de', 'region' => 'eu'],
            ['code' => 'A1VC38T7YXB528', 'country_code' => 'JP', 'name' => 'Amazon.co.jp', 'currency' => 'JPY', 'domain' => 'amazon.co.jp', 'region' => 'fe'],
        ];

        $map = [];
        foreach ($defs as $def) {
            $mp = Marketplace::query()->updateOrCreate(['code' => $def['code']], $def);
            $map[$def['country_code']] = $mp;
        }

        return $map;
    }
}
