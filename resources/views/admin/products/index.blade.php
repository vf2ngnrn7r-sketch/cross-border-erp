@extends('admin.layouts.app')
@section('title', '商品管理')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">商品管理</h1>
<form style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:220px" name="q" value="{{ request('q') }}" placeholder="SKU / ASIN / 标题 / 品牌">
<select class="erp-input" style="max-width:140px" name="type">
<option value="">类型(父/单品)</option>
<option value="parent" @selected(request('type')==='parent')>父体</option>
<option value="child" @selected(request('type')==='child')>子体</option>
<option value="standalone" @selected(request('type')==='standalone')>单品</option>
</select>
<select class="erp-input" style="max-width:120px" name="status">
<option value="">状态</option>
@foreach(['draft','active','archived'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
@endforeach
</select>
<label style="display:flex;align-items:center;gap:.35rem;color:#94a3b8;font-size:.85rem">
<input type="checkbox" name="all" value="1" @checked(request()->boolean('all'))> 显示全部含子体
</label>
<button class="erp-btn" type="submit">搜索</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.products.create') }}">新建商品</a>
</form>

@if($categories->isNotEmpty())
<p style="color:#94a3b8;margin-bottom:1rem;font-size:.85rem">
类目：
@foreach($categories as $c)
<span class="erp-badge" style="margin-right:.25rem">{{ $c->name }} ({{ $c->browse_node_id }})</span>
@endforeach
</p>
@endif

<div class="erp-card">
<table class="erp-table">
<thead><tr><th>图</th><th>SKU</th><th>ASIN</th><th>标题</th><th>类型</th><th>类目</th><th>价格</th><th>Listing</th><th>变体</th><th>状态</th><th></th></tr></thead>
<tbody>
@forelse($products as $p)
<tr>
<td>@if($p->main_image_url)<img src="{{ $p->main_image_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;background:#0f172a" onerror="this.style.display='none'">@else—@endif</td>
<td>{{ $p->sku }}</td>
<td>{{ $p->asin }}</td>
<td>{{ \Illuminate\Support\Str::limit($p->title, 42) }}</td>
<td><span class="erp-badge">{{ $p->product_type }}</span></td>
<td>{{ $p->browse_node_name ?? $p->category }}</td>
<td>@if($p->default_price){{ $p->default_currency }} {{ $p->default_price }}@else—@endif</td>
<td>{{ $p->listings_count }}</td>
<td>{{ $p->children_count }}</td>
<td>{{ $p->status }}</td>
<td>
<a style="color:#7dd3fc" href="{{ route('admin.products.show', $p) }}">详情</a>
· <a style="color:#7dd3fc" href="{{ route('admin.products.edit', $p) }}">编辑</a>
</td>
</tr>
@empty
<tr><td colspan="11" style="color:#94a3b8">暂无商品</td></tr>
@endforelse
</tbody></table>
{{ $products->links() }}
</div>
@endsection
