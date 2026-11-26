<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdPerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertisingController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $campaigns = AdCampaign::query()
            ->with(['amazonAccount', 'adGroups', 'marketplace'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest()
            ->paginate(20);

        $metrics = AdPerformanceMetric::query()
            ->with('campaign')
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('report_date')
            ->limit(30)
            ->get();

        $totals = [
            'impressions' => $metrics->sum('impressions'),
            'clicks' => $metrics->sum('clicks'),
            'cost' => $metrics->sum('cost'),
            'sales' => $metrics->sum('sales'),
            'orders' => $metrics->sum('orders'),
        ];

        return view('admin.advertising.index', compact('campaigns', 'org', 'metrics', 'totals'));
    }

    public function show(Request $request, AdCampaign $campaign): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $campaign->organization_id, 403);
        $campaign->load('adGroups');
        $metrics = AdPerformanceMetric::query()
            ->where('ad_campaign_id', $campaign->id)
            ->orderByDesc('report_date')
            ->get();

        return view('admin.advertising.show', compact('campaign', 'metrics'));
    }
}
