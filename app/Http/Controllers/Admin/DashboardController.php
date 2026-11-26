<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdPerformanceMetric;
use App\Models\BuyerMessage;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\PurchaseOrder;
use App\Models\Settlement;
use App\Models\Shipment;
use App\Models\SupportCase;
use App\Models\SyncJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $orgId = $org?->id;

        $stats = [
            'products' => Product::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'orders' => Order::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'inventory_skus' => Inventory::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'settlements' => Settlement::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'campaigns' => AdCampaign::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'returns' => ProductReturn::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'purchase_orders' => PurchaseOrder::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'shipments' => Shipment::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->count(),
            'unread_messages' => BuyerMessage::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->where('status', 'unread')->count(),
            'open_cases' => SupportCase::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->whereIn('status', ['open', 'pending'])->count(),
            'ad_spend' => (float) AdPerformanceMetric::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->sum('cost'),
            'ad_sales' => (float) AdPerformanceMetric::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))->sum('sales'),
            'sync_pending' => SyncJob::query()->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
                ->whereIn('status', ['pending', 'running'])->count(),
        ];

        $recentOrders = Order::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->latest('purchase_date')
            ->limit(8)
            ->get();

        $recentJobs = SyncJob::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->limit(8)
            ->get();

        $recentShipments = Shipment::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('org', 'stats', 'recentOrders', 'recentJobs', 'recentShipments'));
    }
}
