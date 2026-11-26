@extends('admin.layouts.app')
@section('title', '商品资质')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">商品资质</h1>
<p style="color:#94a3b8;margin-bottom:1rem;font-size:.9rem">证书 / 类目准入 / 检测报告 · 即将过期或已过期 {{ $expiringCount }} 条</p>
<form style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:220px" name="q" value="{{ request('q') }}" placeholder="名称 / ASIN / SKU / 证件号">
<select class="erp-input" style="max-width:140px" name="status">
<option value="">状态</option>
@foreach(['valid','expiring','expired','pending','rejected'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
@endforeach
</select>
<select class="erp-input" style="max-width:160px" name="type">
<option value="">类型</option>
@foreach(['certificate','category_eligibility','safety_test','ce','fcc','fda','other'] as $t)
<option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
@endforeach
</select>
<label style="display:flex;align-items:center;gap:.35rem;color:#94a3b8;font-size:.85rem">
<input type="checkbox" name="expiring" value="1" @checked(request()->boolean('expiring'))> 30 天内到期
</label>
<button class="erp-btn" type="submit">筛选</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.product-qualifications.create') }}">新建</a>
</form>
<div class="erp-card">
<table class="erp-table">
<thead><tr><th>名称</th><th>类型</th><th>ASIN / SKU</th><th>商品</th><th>状态</th><th>到期</th><th></th></tr></thead>
<tbody>
@forelse($items as $row)
<tr>
<td>{{ $row->name }}</td>
<td><span class="erp-badge">{{ $row->qualification_type }}</span></td>
<td>{{ $row->asin }}<br><span style="color:#94a3b8;font-size:.8rem">{{ $row->sku }}</span></td>
<td>@if($row->product)<a style="color:#7dd3fc" href="{{ route('admin.products.show', $row->product) }}">{{ $row->product->sku }}</a>@else—@endif</td>
<td>@if(in_array($row->status, ['expiring','expired'], true))
<span class="erp-badge" style="background:rgba(248,113,113,.2);color:#fca5a5">{{ $row->status }}</span>
@else<span class="erp-badge">{{ $row->status }}</span>@endif</td>
<td>{{ optional($row->expires_at)->format('Y-m-d') ?? '—' }}</td>
<td>
<a style="color:#7dd3fc" href="{{ route('admin.product-qualifications.show', $row) }}">详情</a>
· <a style="color:#7dd3fc" href="{{ route('admin.product-qualifications.edit', $row) }}">编辑</a>
</td>
</tr>
@empty
<tr><td colspan="7" style="color:#94a3b8">暂无商品资质</td></tr>
@endforelse
</tbody>
</table>
</div>
<div style="margin-top:1rem">{{ $items->links() }}</div>
@endsection
