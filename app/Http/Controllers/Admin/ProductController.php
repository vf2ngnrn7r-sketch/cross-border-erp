<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $products = Product::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('sku', 'like', '%'.$request->q.'%')
                    ->orWhere('asin', 'like', '%'.$request->q.'%')
                    ->orWhere('title', 'like', '%'.$request->q.'%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'org'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64'],
            'asin' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
            'brand' => ['nullable', 'string', 'max:128'],
            'category' => ['nullable', 'string', 'max:128'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        Product::query()->create([
            ...$data,
            'organization_id' => $org->id,
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()->route('admin.products.index')->with('success', '商品已创建');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeOrg($request, $product->organization_id);

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOrg($request, $product->organization_id);

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64'],
            'asin' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
            'brand' => ['nullable', 'string', 'max:128'],
            'category' => ['nullable', 'string', 'max:128'],
            'status' => ['required', 'string', 'max:32'],
        ]);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', '商品已更新');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOrg($request, $product->organization_id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', '商品已删除');
    }

    protected function authorizeOrg(Request $request, int $organizationId): void
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $organizationId, 403);
    }
}
