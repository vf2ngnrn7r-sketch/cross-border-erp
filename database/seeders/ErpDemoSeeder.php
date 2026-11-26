<?php

namespace Database\Seeders;

use App\Models\AmazonAccount;
use App\Models\AmazonCredential;
use App\Models\Marketplace;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 演示种子：组织/用户/店铺骨架 + 通过 Fixture 驱动同步落库
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

        // 本地商品主数据（Listing/库存/订单等由 Fixture 同步写入）
        $products = [
            ['sku' => 'SKU-EARBUD-BLK', 'asin' => 'B0FIX001AA', 'title' => 'DemoBrand Wireless Earbuds Pro', 'brand' => 'DemoBrand', 'category' => 'Electronics'],
            ['sku' => 'SKU-STAND-SLV', 'asin' => 'B0FIX002BB', 'title' => 'DemoBrand Desktop Stand', 'brand' => 'DemoBrand', 'category' => 'Home'],
            ['sku' => 'SKU-BOX-SET', 'asin' => 'B0FIX003CC', 'title' => 'DemoBrand Storage Box Set', 'brand' => 'DemoBrand', 'category' => 'Home'],
        ];

        foreach ($products as $row) {
            Product::query()->updateOrCreate(
                ['organization_id' => $org->id, 'sku' => $row['sku']],
                [...$row, 'status' => 'active', 'attributes' => ['source' => 'seeder']]
            );
        }

        // 经 FixtureAmazonSpApiClient 同步：订单/库存/Listing/财务/广告/退货
        $results = app(AmazonSyncService::class)->syncAll($account->fresh());
        foreach ($results as $type => $job) {
            $this->command?->line("  fixture sync {$type}: {$job->status}");
        }

        $this->command?->info('演示账号: admin@example.com / password');
        $this->command?->info('Amazon driver: '.config('amazon.driver'));
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
