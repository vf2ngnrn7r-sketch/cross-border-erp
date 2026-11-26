<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmazonAccount;
use App\Models\Marketplace;
use App\Models\StoreQualification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreQualificationController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $items = StoreQualification::query()
            ->with(['amazonAccount', 'marketplace'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('qualification_type', $request->type))
            ->when($request->account, fn ($q) => $q->where('amazon_account_id', $request->account))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('brand_name', 'like', '%'.$request->q.'%')
                    ->orWhere('category_name', 'like', '%'.$request->q.'%')
                    ->orWhere('document_number', 'like', '%'.$request->q.'%');
            }))
            ->when($request->boolean('expiring'), fn ($q) => $q->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(30))
                ->where('expires_at', '>=', now()->toDateString()))
            ->orderByRaw('expires_at is null')
            ->orderBy('expires_at')
            ->paginate(20)
            ->withQueryString();

        $accounts = AmazonAccount::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();

        $expiringCount = StoreQualification::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->whereIn('status', ['expiring', 'expired'])
            ->count();

        return view('admin.store-qualifications.index', compact('items', 'org', 'accounts', 'expiringCount'));
    }

    public function create(Request $request): View
    {
        $lookups = $this->formLookups($request);
        $item = new StoreQualification;
        $accountId = $request->integer('amazon_account_id') ?: $request->integer('account');
        if ($accountId) {
            $item->amazon_account_id = $accountId;
        }

        return view('admin.store-qualifications.create', [...$lookups, 'item' => $item]);
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $this->payload($request);
        $account = AmazonAccount::query()->findOrFail($data['amazon_account_id']);
        abort_unless($account->organization_id === $org->id, 403);

        $record = StoreQualification::query()->create([
            ...$data,
            'organization_id' => $org->id,
        ]);
        $record->refreshExpiryStatus();

        return redirect()->route('admin.store-qualifications.show', $record)->with('success', '店铺资质已创建');
    }

    public function show(Request $request, StoreQualification $storeQualification): View
    {
        $this->authorizeOrg($request, $storeQualification->organization_id);
        $storeQualification->load(['amazonAccount', 'marketplace']);

        return view('admin.store-qualifications.show', ['item' => $storeQualification]);
    }

    public function edit(Request $request, StoreQualification $storeQualification): View
    {
        $this->authorizeOrg($request, $storeQualification->organization_id);

        return view('admin.store-qualifications.edit', [
            'item' => $storeQualification,
            ...$this->formLookups($request),
        ]);
    }

    public function update(Request $request, StoreQualification $storeQualification): RedirectResponse
    {
        $this->authorizeOrg($request, $storeQualification->organization_id);
        $data = $this->payload($request);
        $account = AmazonAccount::query()->findOrFail($data['amazon_account_id']);
        abort_unless($account->organization_id === $storeQualification->organization_id, 403);

        $storeQualification->update($data);
        $storeQualification->refreshExpiryStatus();

        return redirect()->route('admin.store-qualifications.show', $storeQualification)->with('success', '店铺资质已更新');
    }

    public function destroy(Request $request, StoreQualification $storeQualification): RedirectResponse
    {
        $this->authorizeOrg($request, $storeQualification->organization_id);
        $storeQualification->delete();

        return redirect()->route('admin.store-qualifications.index')->with('success', '店铺资质已删除');
    }

    protected function formLookups(Request $request): array
    {
        $org = $request->user()->resolveOrganization();
        $accounts = AmazonAccount::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();
        $marketplaces = Marketplace::query()->orderBy('name')->get();

        return compact('accounts', 'marketplaces');
    }

    protected function payload(Request $request): array
    {
        $request->validate([
            'amazon_account_id' => ['required', 'integer', 'exists:amazon_accounts,id'],
            'marketplace_id' => ['nullable', 'integer', 'exists:marketplaces,id'],
            'qualification_type' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:128'],
            'category_name' => ['nullable', 'string', 'max:128'],
            'document_number' => ['nullable', 'string', 'max:128'],
            'status' => ['required', 'string', 'max:32'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'file_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        return [
            'amazon_account_id' => $request->integer('amazon_account_id'),
            'marketplace_id' => $request->input('marketplace_id') ?: null,
            'qualification_type' => $request->input('qualification_type'),
            'name' => $request->input('name'),
            'brand_name' => $request->input('brand_name'),
            'category_name' => $request->input('category_name'),
            'document_number' => $request->input('document_number'),
            'status' => $request->input('status'),
            'issued_at' => $request->input('issued_at'),
            'expires_at' => $request->input('expires_at'),
            'file_url' => $request->input('file_url'),
            'notes' => $request->input('notes'),
        ];
    }

    protected function authorizeOrg(Request $request, int $organizationId): void
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $organizationId, 403);
    }
}
