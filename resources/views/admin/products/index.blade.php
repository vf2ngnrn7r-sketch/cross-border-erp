@extends('admin.layouts.app')
@section('title', '商品目录')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">商品目录</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:280px" name="q" value="{{ request('q') }}" placeholder="SKU / ASIN / 标题">
<button class="erp-btn" type="submit">搜索</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.products.create') }}">新建商品</a>
</form>
<div class="erp-card">
<table class="erp-table">
<thead><tr><th>SKU</th><th>ASIN</th><th>标题</th><th>品牌</th><th>状态</th><th></th></tr></thead>
<tbody>
@forelse($products as $p)
<tr>
<td>{{ $p->sku }}</td><td>{{ $p->asin }}</td><td>{{ $p->title }}</td><td>{{ $p->brand }}</td>
<td><span class="erp-badge">{{ $p->status }}</span></td>
<td><a style="color:#7dd3fc" href="{{ route('admin.products.edit', $p) }}">编辑</a></td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无商品</td></tr>
@endforelse
</tbody></table>
{{ $products->withQueryString()->links() }}
</div>
@endsection
