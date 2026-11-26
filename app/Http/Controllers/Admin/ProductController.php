<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $products = Product::query()
            ->with(['parent', 'images', 'listings.marketplace'])
            ->withCount(['children', 'listings', 'inventories'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->type, fn ($q) => $q->where('product_type', $request->type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('sku', 'like', '%'.$request->q.'%')
                    ->orWhere('asin', 'like', '%'.$request->q.'%')
                    ->orWhere('title', 'like', '%'.$request->q.'%')
                    ->orWhere('brand', 'like', '%'.$request->q.'%');
            }))
            // 列表默认展示 parent + standalone，child 在详情中看
            ->when(! $request->boolean('all') && ! $request->type, fn ($q) => $q->whereIn('product_type', ['parent', 'standalone']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();

        return view('admin.products.index', compact('products', 'org', 'categories'));
    }

    public function show(Request $request, Product $product): View
    {
        $this->authorizeOrg($request, $product->organization_id);
        $product->load([
            'parent',
            'children.images',
            'children.listings.marketplace',
            'children.inventories',
            'images',
            'listings.marketplace',
            'listings.amazonAccount',
            'inventories',
            'qualifications.marketplace',
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function create(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $parents = Product::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->where('product_type', 'parent')
            ->orderBy('title')
            ->get();
        $categories = ProductCategory::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('parents', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org, 403);

        $data = $this->productPayload($request);
        $product = Product::query()->create([
            ...$data,
            'organization_id' => $org->id,
        ]);

        if ($url = $request->input('main_image_url')) {
            ProductImage::query()->create([
                'organization_id' => $org->id,
                'product_id' => $product->id,
                'url' => $url,
                'variant' => 'MAIN',
                'position' => 0,
                'is_main' => true,
            ]);
        }

        return redirect()->route('admin.products.show', $product)->with('success', '商品已创建');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeOrg($request, $product->organization_id);
        $org = $request->user()->resolveOrganization();
        $parents = Product::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->where('product_type', 'parent')
            ->where('id', '!=', $product->id)
            ->orderBy('title')
            ->get();
        $categories = ProductCategory::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'parents', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOrg($request, $product->organization_id);
        $product->update($this->productPayload($request));

        if ($url = $request->input('main_image_url')) {
            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'is_main' => true],
                [
                    'organization_id' => $product->organization_id,
                    'url' => $url,
                    'variant' => 'MAIN',
                    'position' => 0,
                    'is_main' => true,
                ]
            );
            $product->forceFill(['main_image_url' => $url])->save();
        }

        return redirect()->route('admin.products.show', $product)->with('success', '商品已更新');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOrg($request, $product->organization_id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', '商品已删除');
    }

    protected function productPayload(Request $request): array
    {
        $request->validate([
            'sku' => ['required', 'string', 'max:64'],
            'asin' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
            'brand' => ['nullable', 'string', 'max:128'],
            'category' => ['nullable', 'string', 'max:128'],
            'browse_node_id' => ['nullable', 'string', 'max:64'],
            'browse_node_name' => ['nullable', 'string', 'max:128'],
            'amazon_product_type' => ['nullable', 'string', 'max:64'],
            'product_type' => ['required', 'in:parent,child,standalone'],
            'parent_id' => ['nullable', 'integer', 'exists:products,id'],
            'parent_sku' => ['nullable', 'string', 'max:64'],
            'parent_asin' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'main_image_url' => ['nullable', 'string', 'max:500'],
            'default_cost' => ['nullable', 'numeric'],
            'default_price' => ['nullable', 'numeric'],
            'default_currency' => ['nullable', 'string', 'max:3'],
            'status' => ['required', 'string', 'max:32'],
            'attr_color' => ['nullable', 'string', 'max:64'],
            'attr_size' => ['nullable', 'string', 'max:64'],
            'bullet_points_text' => ['nullable', 'string'],
        ]);

        return [
            'sku' => $request->input('sku'),
            'asin' => $request->input('asin'),
            'title' => $request->input('title'),
            'brand' => $request->input('brand'),
            'category' => $request->input('category'),
            'browse_node_id' => $request->input('browse_node_id'),
            'browse_node_name' => $request->input('browse_node_name'),
            'amazon_product_type' => $request->input('amazon_product_type'),
            'product_type' => $request->input('product_type'),
            'parent_id' => $request->input('parent_id') ?: null,
            'parent_sku' => $request->input('parent_sku'),
            'parent_asin' => $request->input('parent_asin'),
            'description' => $request->input('description'),
            'main_image_url' => $request->input('main_image_url'),
            'default_cost' => $request->input('default_cost'),
            'default_price' => $request->input('default_price'),
            'default_currency' => $request->input('default_currency', 'USD'),
            'status' => $request->input('status'),
            'bullet_points' => $this->parseLines($request->input('bullet_points_text')),
            'attributes' => $this->parseAttributes($request),
        ];
    }

    protected function parseLines(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();
    }

    protected function parseAttributes(Request $request): array
    {
        $attrs = [];
        if ($request->filled('attr_color')) {
            $attrs['color'] = $request->input('attr_color');
        }
        if ($request->filled('attr_size')) {
            $attrs['size'] = $request->input('attr_size');
        }

        return $attrs;
    }

    protected function authorizeOrg(Request $request, int $organizationId): void
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $organizationId, 403);
    }
}
