@extends('admin.layouts.app')
@section('title', 'Listing 详情')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">{{ $listing->title }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">
{{ $listing->seller_sku }} · {{ $listing->asin }} ·
站点 {{ $listing->marketplace?->name }} ·
<span class="erp-badge">{{ $listing->status }}</span>
{{ $listing->listing_status }}
</p>
@if($listing->suppress_reason)
<div class="erp-flash" style="background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.35);color:#fca5a5">抑制原因：{{ $listing->suppress_reason }}</div>
@endif
<div class="erp-card" style="margin-bottom:1rem">
<table class="erp-table">
<tr><th>价格</th><td>{{ $listing->currency }} {{ $listing->price }}</td></tr>
<tr><th>数量（库存挂钩）</th><td>{{ $listing->quantity ?? '—' }}</td></tr>
<tr><th>履约渠道</th><td>{{ $listing->fulfillment_channel }}</td></tr>
<tr><th>Product Type</th><td>{{ $listing->amazon_product_type }}</td></tr>
<tr><th>Browse Node</th><td>{{ $listing->browse_node_id }}</td></tr>
<tr><th>状态标志</th><td><code>{{ json_encode($listing->status_flags) }}</code></td></tr>
<tr><th>属性</th><td><code>{{ json_encode($listing->attributes, JSON_UNESCAPED_UNICODE) }}</code></td></tr>
<tr><th>商品主数据</th><td>
@if($listing->product)
<a style="color:#7dd3fc" href="{{ route('admin.products.show', $listing->product) }}">{{ $listing->product->sku }}</a>
@else — @endif
</td></tr>
</table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">关联库存</h2>
@forelse($listing->inventories as $inv)
<p>{{ $inv->fulfillment_channel }} · 可售 {{ $inv->quantity_available }} · 在途 {{ $inv->quantity_inbound }}</p>
@empty
<p style="color:#94a3b8">无库存行</p>
@endforelse
</div>
@endsection
