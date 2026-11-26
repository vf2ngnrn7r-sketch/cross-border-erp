<?php

namespace Database\Seeders;

use App\Models\AmazonAccount;
use App\Models\AmazonCredential;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Amazon\AmazonSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 演示种子：组织/用户/店铺 + 内部采购仓配 + Fixture 同步
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
            ['sku' => 'SKU-EARBUD-BLK', 'asin' => 'B0FIX001AA', 'title' => 'DemoBrand Wireless Earbuds Pro', 'brand' => 'DemoBrand', 'category' => 'Electronics'],
            ['sku' => 'SKU-STAND-SLV', 'asin' => 'B0FIX002BB', 'title' => 'DemoBrand Desktop Stand', 'brand' => 'DemoBrand', 'category' => 'Home'],
            ['sku' => 'SKU-BOX-SET', 'asin' => 'B0FIX003CC', 'title' => 'DemoBrand Storage Box Set', 'brand' => 'DemoBrand', 'category' => 'Home'],
        ];

        $productMap = [];
        foreach ($products as $row) {
            $productMap[$row['sku']] = Product::query()->updateOrCreate(
                ['organization_id' => $org->id, 'sku' => $row['sku']],
                [...$row, 'status' => 'active', 'attributes' => ['source' => 'seeder']]
            );
        }

        // 内部 ERP：仓库
        $localWh = Warehouse::query()->updateOrCreate(
            ['organization_id' => $org->id, 'code' => 'CN-SZ-01'],
            ['name' => '深圳本地仓', 'type' => 'local', 'country' => 'CN', 'address' => 'Nanshan, Shenzhen', 'status' => 'active']
        );
        Warehouse::query()->updateOrCreate(
            ['organization_id' => $org->id, 'code' => 'AMZ-FBA-NA'],
            ['name' => 'Amazon FBA NA', 'type' => 'fba', 'country' => 'US', 'address' => 'Amazon FC Network', 'status' => 'active']
        );

        // 内部 ERP：供应商 + 采购单
        $supplier = Supplier::query()->updateOrCreate(
            ['organization_id' => $org->id, 'code' => 'SUP-DG-01'],
            [
                'name' => '东莞演示电子厂',
                'contact_name' => '张工',
                'email' => 'supplier@example.invalid',
                'phone' => '+86-769-0000-0000',
                'country' => 'CN',
                'payment_terms' => 'Net 30',
                'status' => 'active',
            ]
        );

        $po = PurchaseOrder::query()->updateOrCreate(
            ['organization_id' => $org->id, 'po_number' => 'PO-2026-0001'],
            [
                'supplier_id' => $supplier->id,
                'status' => 'ordered',
                'currency' => 'USD',
                'total_amount' => 1850,
                'ordered_at' => now()->subDays(12),
                'expected_at' => now()->addDays(5),
                'notes' => '耳机 + 收纳盒补货，关联 FBA 入库货件',
            ]
        );

        PurchaseOrderItem::query()->updateOrCreate(
            ['purchase_order_id' => $po->id, 'sku' => 'SKU-EARBUD-BLK'],
            [
                'product_id' => $productMap['SKU-EARBUD-BLK']->id,
                'title' => 'DemoBrand Wireless Earbuds Pro',
                'quantity' => 200,
                'received_quantity' => 0,
                'unit_cost' => 6.50,
            ]
        );
        PurchaseOrderItem::query()->updateOrCreate(
            ['purchase_order_id' => $po->id, 'sku' => 'SKU-BOX-SET'],
            [
                'product_id' => $productMap['SKU-BOX-SET']->id,
                'title' => 'DemoBrand Storage Box Set',
                'quantity' => 100,
                'received_quantity' => 0,
                'unit_cost' => 5.50,
            ]
        );

        // Fixture 同步（含 FBA inbound / messaging / ad metrics）
        $results = app(AmazonSyncService::class)->syncAll($account->fresh());
        foreach ($results as $type => $job) {
            $this->command?->line("  fixture sync {$type}: {$job->status}");
        }

        // 将 FBA 入库货件关联到采购单
        Shipment::query()
            ->where('amazon_account_id', $account->id)
            ->where('external_shipment_id', 'FBA15FIX0001')
            ->update(['purchase_order_id' => $po->id, 'warehouse_id' => $localWh->id]);

        // FBM 出库货件（内部演示，绑定 MFN 订单）
        $mfnOrder = Order::query()->where('amazon_order_id', '111-2211990-4455667')->first();
        if ($mfnOrder) {
            $outbound = Shipment::query()->updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'external_shipment_id' => 'FBM-OUT-FIX-001',
                ],
                [
                    'amazon_account_id' => $account->id,
                    'warehouse_id' => $localWh->id,
                    'order_id' => $mfnOrder->id,
                    'shipment_type' => 'fbm_outbound',
                    'reference' => $mfnOrder->amazon_order_id,
                    'status' => 'SHIPPED',
                    'carrier' => 'USPS',
                    'tracking_number' => '9400FIXTRACKFBM01',
                    'shipped_at' => now()->subHours(8),
                    'expected_arrival' => now()->addDays(3),
                ]
            );
            ShipmentItem::query()->updateOrCreate(
                ['shipment_id' => $outbound->id, 'sku' => 'SKU-BOX-SET'],
                ['asin' => 'B0FIX003CC', 'quantity' => 1, 'quantity_received' => 0]
            );
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
