<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\ProductQualification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductQualificationController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $items = ProductQualification::query()
            ->with(['product', 'marketplace'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('qualification_type', $request->type))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('asin', 'like', '%'.$request->q.'%')
                    ->orWhere('sku', 'like', '%'.$request->q.'%')
                    ->orWhere('document_number', 'like', '%'.$request->q.'%');
            }))
            ->when($request->boolean('expiring'), fn ($q) => $q->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(30))
                ->where('expires_at', '>=', now()->toDateString()))
            ->orderByRaw('expires_at is null')
            ->orderBy('expires_at')
            ->paginate(20)
            ->withQueryString();

        $expiringCount = ProductQualification::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->whereIn('status', ['expiring', 'expired'])
            ->count();

        return view('admin.product-qualifications.index', compact('items', 'org', 'expiringCount'));
    }

    public function create(Request $request): View
    {
        return view('admin.product-qualifications.create', $this->formLookups($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $this->payload($request);
        $record = ProductQualification::query()->create([
            ...$data,
            'organization_id' => $org->id,
        ]);
        $record->refreshExpiryStatus();

        return redirect()->route('admin.product-qualifications.show', $record)->with('success', '商品资质已创建');
    }

    public function show(Request $request, ProductQualification $productQualification): View
    {
        $this->authorizeOrg($request, $productQualification->organization_id);
        $productQualification->load(['product', 'marketplace']);

        return view('admin.product-qualifications.show', ['item' => $productQualification]);
    }

    public function edit(Request $request, ProductQualification $productQualification): View
    {
        $this->authorizeOrg($request, $productQualification->organization_id);

        return view('admin.product-qualifications.edit', [
            'item' => $productQualification,
            ...$this->formLookups($request),
        ]);
    }

    public function update(Request $request, ProductQualification $productQualification): RedirectResponse
    {
        $this->authorizeOrg($request, $productQualification->organization_id);
        $productQualification->update($this->payload($request));
        $productQualification->refreshExpiryStatus();

        return redirect()->route('admin.product-qualifications.show', $productQualification)->with('success', '商品资质已更新');
    }

    public function destroy(Request $request, ProductQualification $productQualification): RedirectResponse
    {
        $this->authorizeOrg($request, $productQualification->organization_id);
        $productQualification->delete();

        return redirect()->route('admin.product-qualifications.index')->with('success', '商品资质已删除');
    }

    protected function formLookups(Request $request): array
    {
        $org = $request->user()->resolveOrganization();
        $products = Product::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('sku')
            ->get(['id', 'sku', 'asin', 'title']);
        $marketplaces = Marketplace::query()->orderBy('name')->get();

        return compact('products', 'marketplaces');
    }

    protected function payload(Request $request): array
    {
        $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'marketplace_id' => ['nullable', 'integer', 'exists:marketplaces,id'],
            'asin' => ['nullable', 'string', 'max:20'],
            'sku' => ['nullable', 'string', 'max:64'],
            'qualification_type' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:128'],
            'issuer' => ['nullable', 'string', 'max:128'],
            'status' => ['required', 'string', 'max:32'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'file_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = $request->filled('product_id')
            ? Product::query()->find($request->integer('product_id'))
            : null;

        return [
            'product_id' => $product?->id,
            'marketplace_id' => $request->input('marketplace_id') ?: null,
            'asin' => $request->input('asin') ?: $product?->asin,
            'sku' => $request->input('sku') ?: $product?->sku,
            'qualification_type' => $request->input('qualification_type'),
            'name' => $request->input('name'),
            'document_number' => $request->input('document_number'),
            'issuer' => $request->input('issuer'),
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
