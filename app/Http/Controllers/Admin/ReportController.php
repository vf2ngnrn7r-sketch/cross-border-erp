<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdPerformanceMetric;
use App\Models\BuyerMessage;
use App\Models\FinancialEvent;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\SupportCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** 经营汇总报表页 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $orgId = $org?->id;

        $orderSummary = Order::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->selectRaw('order_status, COUNT(*) as cnt, COALESCE(SUM(order_total),0) as amount')
            ->groupBy('order_status')
            ->get();

        $inventorySummary = Inventory::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->selectRaw('fulfillment_channel, SUM(quantity_available) as available, SUM(quantity_inbound) as inbound, COUNT(*) as skus')
            ->groupBy('fulfillment_channel')
            ->get();

        $financeTotal = FinancialEvent::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->sum('amount');

        $financeByType = FinancialEvent::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->selectRaw('event_type, SUM(amount) as amount')
            ->groupBy('event_type')
            ->get();

        $adTotals = AdPerformanceMetric::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->selectRaw('SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(cost) as cost, SUM(sales) as sales, SUM(`orders`) as orders')
            ->first();

        $adByCampaign = AdPerformanceMetric::query()
            ->with('campaign')
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->select('external_campaign_id', 'ad_campaign_id', DB::raw('SUM(impressions) as impressions'), DB::raw('SUM(clicks) as clicks'), DB::raw('SUM(cost) as cost'), DB::raw('SUM(sales) as sales'), DB::raw('SUM(`orders`) as orders'))
            ->groupBy('external_campaign_id', 'ad_campaign_id')
            ->get();

        $openPos = PurchaseOrder::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->whereIn('status', ['draft', 'ordered', 'partial'])
            ->count();

        $openShipments = Shipment::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->whereNotIn('status', ['CLOSED', 'CANCELLED', 'delivered'])
            ->count();

        $unreadMessages = BuyerMessage::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->where('status', 'unread')
            ->count();

        $openCases = SupportCase::query()
            ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
            ->whereIn('status', ['open', 'pending'])
            ->count();

        return view('admin.reports.index', compact(
            'org',
            'orderSummary',
            'inventorySummary',
            'financeTotal',
            'financeByType',
            'adTotals',
            'adByCampaign',
            'openPos',
            'openShipments',
            'unreadMessages',
            'openCases'
        ));
    }
}
