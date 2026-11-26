<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
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

        return view('admin.advertising.index', compact('campaigns', 'org'));
    }

    public function show(Request $request, AdCampaign $campaign): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $campaign->organization_id, 403);
        $campaign->load('adGroups');

        return view('admin.advertising.show', compact('campaign'));
    }
}
