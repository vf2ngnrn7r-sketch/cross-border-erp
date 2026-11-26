@extends('admin.layouts.app')
@section('title', $product->title)
@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem;flex-wrap:wrap">
<div>
<h1 style="font-size:1.4rem;margin-bottom:.35rem">{{ $product->title }}</h1>
<p style="color:#94a3b8">SKU {{ $product->sku }} · ASIN {{ $product->asin ?? '—' }} · <span class="erp-badge">{{ $product->product_type }}</span> · {{ $product->status }}</p>
</div>
<div>
<a class="erp-btn" href="{{ route('admin.products.edit', $product) }}">编辑</a>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.products.index') }}">返回列表</a>
</div>
</div>

<div style="display:grid;grid-template-columns:200px 1fr;gap:1rem;margin-bottom:1rem">
<div class="erp-card" style="text-align:center">
@if($product->main_image_url)
<img src="{{ $product->main_image_url }}" alt="" style="max-width:100%;border-radius:8px;background:#0f172a" onerror="this.alt='图片占位'">
@else
<p style="color:#94a3b8">无主图</p>
@endif
</div>
<div class="erp-card">
<table class="erp-table">
<tr><th>品牌</th><td>{{ $product->brand }}</td></tr>
<tr><th>Browse Node</th><td>{{ $product->browse_node_name }} ({{ $product->browse_node_id }})</td></tr>
<tr><th>Product Type</th><td>{{ $product->amazon_product_type }}</td></tr>
<tr><th>默认售价</th><td>{{ $product->default_currency }} {{ $product->default_price }}</td></tr>
<tr><th>成本</th><td>{{ $product->default_cost }}</td></tr>
<tr><th>变体主题</th><td>{{ is_array($product->variation_theme) ? implode(', ', $product->variation_theme) : '—' }}</td></tr>
<tr><th>属性</th><td><code style="color:#cbd5e1">{{ json_encode($product->attributes, JSON_UNESCAPED_UNICODE) }}</code></td></tr>
<tr><th>库存合计</th><td>{{ $product->inventories->sum('quantity_available') }}</td></tr>
</table>
@if($product->description)
<p style="margin-top:.75rem;color:#cbd5e1">{{ $product->description }}</p>
@endif
@if($product->bullet_points)
<ul style="margin-top:.5rem;color:#94a3b8;padding-left:1.2rem">
@foreach($product->bullet_points as $bp)<li>{{ $bp }}</li>@endforeach
</ul>
@endif
</div>
</div>

@if($product->images->isNotEmpty())
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">图片</h2>
<div style="display:flex;gap:.5rem;flex-wrap:wrap">
@foreach($product->images as $img)
<div style="width:90px;text-align:center">
<img src="{{ $img->url }}" alt="" style="width:90px;height:90px;object-fit:cover;border-radius:6px;background:#0f172a" onerror="this.style.opacity=.3">
<div style="font-size:.7rem;color:#94a3b8">{{ $img->variant }}</div>
</div>
@endforeach
</div>
</div>
@endif

@if($product->isParent() && $product->children->isNotEmpty())
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">子体变体 ({{ $product->children->count() }})</h2>
<table class="erp-table">
<thead><tr><th>SKU</th><th>ASIN</th><th>标题</th><th>颜色/尺码</th><th>价格</th><th>库存</th><th></th></tr></thead>
<tbody>
@foreach($product->children as $child)
<tr>
<td>{{ $child->sku }}</td><td>{{ $child->asin }}</td>
<td>{{ \Illuminate\Support\Str::limit($child->title, 36) }}</td>
<td>{{ ($child->attributes['color'] ?? '—').' / '.($child->attributes['size'] ?? '—') }}</td>
<td>{{ $child->default_price }}</td>
<td>{{ $child->inventories->sum('quantity_available') }}</td>
<td><a style="color:#7dd3fc" href="{{ route('admin.products.show', $child) }}">详情</a></td>
</tr>
@endforeach
</tbody></table>
</div>
@endif

@if($product->parent)
<p style="margin-bottom:1rem;color:#94a3b8">父体：<a style="color:#7dd3fc" href="{{ route('admin.products.show', $product->parent) }}">{{ $product->parent->sku }} — {{ $product->parent->title }}</a></p>
@endif

<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">各站点 Listing</h2>
<table class="erp-table">
<thead><tr><th>站点</th><th>Seller SKU</th><th>状态</th><th>Listing Status</th><th>价格</th><th>数量</th><th>渠道</th><th>抑制原因</th></tr></thead>
<tbody>
@forelse($product->listings as $l)
<tr>
<td>{{ $l->marketplace?->country_code ?? $l->marketplace_id }}</td>
<td>{{ $l->seller_sku }}</td>
<td><span class="erp-badge">{{ $l->status }}</span></td>
<td>{{ $l->listing_status }}</td>
<td>{{ $l->currency }} {{ $l->price }}</td>
<td>{{ $l->quantity ?? '—' }}</td>
<td>{{ $l->fulfillment_channel }}</td>
<td style="color:#fca5a5">{{ $l->suppress_reason }}</td>
</tr>
@empty
<tr><td colspan="8" style="color:#94a3b8">暂无 Listing（可执行 amazon:sync --scope=listings）</td></tr>
@endforelse
</tbody></table>
</div>

<div class="erp-card">
<h2 style="margin-bottom:.75rem">库存挂钩</h2>
<table class="erp-table">
<thead><tr><th>SKU</th><th>渠道</th><th>可售</th><th>预留</th><th>在途</th><th>不可售</th></tr></thead>
<tbody>
@forelse($product->inventories as $inv)
<tr>
<td>{{ $inv->sku }}</td><td>{{ $inv->fulfillment_channel }}</td>
<td>{{ $inv->quantity_available }}</td><td>{{ $inv->quantity_reserved }}</td>
<td>{{ $inv->quantity_inbound }}</td><td>{{ $inv->quantity_unfulfillable }}</td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无库存记录</td></tr>
@endforelse
</tbody></table>
</div>
@endsection
