<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAmazonAccountJob;
use App\Models\AmazonAccount;
use App\Models\AmazonCredential;
use App\Models\Marketplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmazonAccountController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $accounts = AmazonAccount::query()
            ->with(['marketplaces', 'credential'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest()
            ->paginate(20);

        return view('admin.amazon-accounts.index', compact('accounts', 'org'));
    }

    public function create(Request $request): View
    {
        $marketplaces = Marketplace::query()->orderBy('name')->get();

        return view('admin.amazon-accounts.create', compact('marketplaces'));
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403, '请先加入组织');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'seller_id' => ['nullable', 'string', 'max:64'],
            'marketplace_ids' => ['array'],
            'marketplace_ids.*' => ['integer', 'exists:marketplaces,id'],
            // 凭证仅接受占位，不强制
            'lwa_client_id' => ['nullable', 'string'],
            'api_region' => ['nullable', 'string', 'max:8'],
        ]);

        $account = AmazonAccount::query()->create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'seller_id' => $data['seller_id'] ?? null,
            'status' => 'active',
        ]);

        if (! empty($data['marketplace_ids'])) {
            $account->marketplaces()->sync($data['marketplace_ids']);
        }

        AmazonCredential::query()->create([
            'amazon_account_id' => $account->id,
            'lwa_client_id' => $data['lwa_client_id'] ?? 'amzn1.application-oa2-client.PLACEHOLDER',
            'lwa_client_secret' => 'PLACEHOLDER_SECRET',
            'lwa_refresh_token' => 'Atzr|PLACEHOLDER_REFRESH_TOKEN',
            'aws_access_key_id' => 'AKIA_PLACEHOLDER',
            'aws_secret_access_key' => 'PLACEHOLDER_AWS_SECRET',
            'api_region' => $data['api_region'] ?? 'na',
            'status' => 'draft',
        ]);

        return redirect()->route('admin.amazon-accounts.index')
            ->with('success', '亚马逊账号已创建（凭证为占位符）');
    }

    public function show(Request $request, AmazonAccount $amazonAccount): View
    {
        $this->authorizeOrg($request, $amazonAccount->organization_id);
        $amazonAccount->load(['marketplaces', 'credential', 'orders' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.amazon-accounts.show', compact('amazonAccount'));
    }

    public function sync(Request $request, AmazonAccount $amazonAccount): RedirectResponse
    {
        $this->authorizeOrg($request, $amazonAccount->organization_id);
        $scope = $request->input('scope', 'all');
        SyncAmazonAccountJob::dispatch($amazonAccount->id, $scope);

        return back()->with('success', '同步任务已投递队列');
    }

    protected function authorizeOrg(Request $request, int $organizationId): void
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $organizationId, 403);
    }
}
