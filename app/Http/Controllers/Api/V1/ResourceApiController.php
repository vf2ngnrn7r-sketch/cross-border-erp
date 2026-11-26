<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdPerformanceMetric;
use App\Models\AmazonAccount;
use App\Models\BuyerMessage;
use App\Models\Inventory;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductQualification;
use App\Models\ProductReturn;
use App\Models\StoreQualification;
use App\Models\PurchaseOrder;
use App\Models\Settlement;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\SupportCase;
use App\Models\SyncJob;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 核心实体只读 / 简易 CRUD JSON API（按当前登录用户组织隔离）
 */
class ResourceApiController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        return $this->paginate(
            $request,
            Product::query()->with(['images', 'children', 'listings'])->withCount('children')
        );
    }

    public function product(Request $request, Product $product): JsonResponse
    {
        $this->assertOrg($request, $product->organization_id);
        $product->load(['images', 'children.images', 'listings.marketplace', 'inventories', 'parent']);

        return response()->json($product);
    }

    public function productVariants(Request $request, Product $product): JsonResponse
    {
        $this->assertOrg($request, $product->organization_id);

        return response()->json(
            $product->children()->with(['images', 'listings', 'inventories'])->get()
        );
    }

    public function categories(Request $request): JsonResponse
    {
        return $this->paginate($request, ProductCategory::query());
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64'],
            'asin' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
            'brand' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'product_type' => ['nullable', 'in:parent,child,standalone'],
            'parent_id' => ['nullable', 'integer'],
            'browse_node_id' => ['nullable', 'string'],
            'browse_node_name' => ['nullable', 'string'],
            'amazon_product_type' => ['nullable', 'string'],
            'default_price' => ['nullable', 'numeric'],
            'default_cost' => ['nullable', 'numeric'],
            'main_image_url' => ['nullable', 'string'],
            'attributes' => ['nullable', 'array'],
            'status' => ['nullable', 'string'],
        ]);

        $product = Product::query()->create([
            ...$data,
            'organization_id' => $org->id,
            'product_type' => $data['product_type'] ?? 'standalone',
            'status' => $data['status'] ?? 'active',
        ]);

        return response()->json($product->load('images'), 201);
    }

    public function orders(Request $request): JsonResponse
    {
        return $this->paginate($request, Order::query()->with(['items', 'fulfillments']));
    }

    public function order(Request $request, Order $order): JsonResponse
    {
        $this->assertOrg($request, $order->organization_id);
        $order->load(['items', 'fulfillments', 'amazonAccount']);

        return response()->json($order);
    }

    public function inventories(Request $request): JsonResponse
    {
        return $this->paginate($request, Inventory::query());
    }

    public function listings(Request $request): JsonResponse
    {
        return $this->paginate($request, Listing::query()->with(['product', 'marketplace']));
    }

    public function listing(Request $request, Listing $listing): JsonResponse
    {
        $this->assertOrg($request, $listing->organization_id);
        $listing->load(['product.images', 'marketplace', 'inventories']);

        return response()->json($listing);
    }

    public function amazonAccounts(Request $request): JsonResponse
    {
        return $this->paginate($request, AmazonAccount::query()->with(['marketplaces', 'credential']));
    }

    public function settlements(Request $request): JsonResponse
    {
        return $this->paginate($request, Settlement::query()->with('financialEvents'));
    }

    public function campaigns(Request $request): JsonResponse
    {
        return $this->paginate($request, AdCampaign::query()->with('adGroups'));
    }

    public function adMetrics(Request $request): JsonResponse
    {
        return $this->paginate($request, AdPerformanceMetric::query()->with('campaign'));
    }

    public function returns(Request $request): JsonResponse
    {
        return $this->paginate($request, ProductReturn::query());
    }

    public function syncJobs(Request $request): JsonResponse
    {
        return $this->paginate($request, SyncJob::query());
    }

    public function suppliers(Request $request): JsonResponse
    {
        return $this->paginate($request, Supplier::query());
    }

    public function purchaseOrders(Request $request): JsonResponse
    {
        return $this->paginate($request, PurchaseOrder::query()->with(['supplier', 'items']));
    }

    public function warehouses(Request $request): JsonResponse
    {
        return $this->paginate($request, Warehouse::query());
    }

    public function shipments(Request $request): JsonResponse
    {
        return $this->paginate($request, Shipment::query()->with('items'));
    }

    public function messages(Request $request): JsonResponse
    {
        return $this->paginate($request, BuyerMessage::query());
    }

    public function cases(Request $request): JsonResponse
    {
        return $this->paginate($request, SupportCase::query());
    }

    public function productQualifications(Request $request): JsonResponse
    {
        return $this->paginate(
            $request,
            ProductQualification::query()->with(['product', 'marketplace'])
        );
    }

    public function productQualification(Request $request, ProductQualification $productQualification): JsonResponse
    {
        $this->assertOrg($request, $productQualification->organization_id);
        $productQualification->load(['product', 'marketplace']);

        return response()->json($productQualification);
    }

    public function storeProductQualification(Request $request): JsonResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'marketplace_id' => ['nullable', 'integer', 'exists:marketplaces,id'],
            'asin' => ['nullable', 'string', 'max:20'],
            'sku' => ['nullable', 'string', 'max:64'],
            'qualification_type' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string'],
            'issuer' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:32'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'file_url' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = ProductQualification::query()->create([
            ...$data,
            'organization_id' => $org->id,
            'status' => $data['status'] ?? 'valid',
        ]);
        $record->refreshExpiryStatus();

        return response()->json($record->load(['product', 'marketplace']), 201);
    }

    public function storeQualifications(Request $request): JsonResponse
    {
        return $this->paginate(
            $request,
            StoreQualification::query()->with(['amazonAccount', 'marketplace'])
        );
    }

    public function storeQualification(Request $request, StoreQualification $storeQualification): JsonResponse
    {
        $this->assertOrg($request, $storeQualification->organization_id);
        $storeQualification->load(['amazonAccount', 'marketplace']);

        return response()->json($storeQualification);
    }

    public function storeStoreQualification(Request $request): JsonResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $request->validate([
            'amazon_account_id' => ['required', 'integer', 'exists:amazon_accounts,id'],
            'marketplace_id' => ['nullable', 'integer', 'exists:marketplaces,id'],
            'qualification_type' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string'],
            'category_name' => ['nullable', 'string'],
            'document_number' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:32'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'file_url' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $account = AmazonAccount::query()->findOrFail($data['amazon_account_id']);
        abort_unless($account->organization_id === $org->id, 403);

        $record = StoreQualification::query()->create([
            ...$data,
            'organization_id' => $org->id,
            'status' => $data['status'] ?? 'approved',
        ]);
        $record->refreshExpiryStatus();

        return response()->json($record->load(['amazonAccount', 'marketplace']), 201);
    }

    public function reportSummary(Request $request): JsonResponse
    {
        $org = $request->user()->resolveOrganization();
        $orgId = $org?->id;

        return response()->json([
            'orders' => Order::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'inventory_skus' => Inventory::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'purchase_orders' => PurchaseOrder::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'shipments' => Shipment::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'unread_messages' => BuyerMessage::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->where('status', 'unread')->count(),
            'open_cases' => SupportCase::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->whereIn('status', ['open', 'pending'])->count(),
            'product_qualifications_expiring' => ProductQualification::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->whereIn('status', ['expiring', 'expired'])->count(),
            'store_qualifications_expiring' => StoreQualification::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->whereIn('status', ['expiring', 'expired'])->count(),
            'ad' => [
                'cost' => (float) AdPerformanceMetric::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->sum('cost'),
                'sales' => (float) AdPerformanceMetric::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->sum('sales'),
                'clicks' => (int) AdPerformanceMetric::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->sum('clicks'),
            ],
        ]);
    }

    protected function paginate(Request $request, $query): JsonResponse
    {
        $org = $request->user()->resolveOrganization();
        if ($org) {
            $query->where('organization_id', $org->id);
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 20)));
    }

    protected function assertOrg(Request $request, int $organizationId): void
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $organizationId, 403);
    }
}
