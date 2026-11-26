@extends('admin.layouts.app')
@section('title', '退货与客服')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">退货 / A-to-Z / 买家反馈</h1>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">退货</h2>
<table class="erp-table"><thead><tr><th>Return ID</th><th>SKU</th><th>原因</th><th>状态</th><th>数量</th><th>日期</th></tr></thead>
<tbody>
@forelse($returns as $r)
<tr><td>{{ $r->return_id }}</td><td>{{ $r->seller_sku }}</td><td>{{ $r->reason }}</td>
<td><span class="erp-badge">{{ $r->status }}</span></td><td>{{ $r->quantity }}</td>
<td>{{ optional($r->return_date)->format('Y-m-d') }}</td></tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无退货</td></tr>
@endforelse
</tbody></table>{{ $returns->links() }}
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
<div class="erp-card">
<h2 style="margin-bottom:.75rem">A-to-Z 索赔</h2>
@forelse($claims as $c)
<p>{{ $c->claim_id }} · {{ $c->reason }} · <span class="erp-badge">{{ $c->status }}</span></p>
@empty
<p style="color:#94a3b8">暂无索赔</p>
@endforelse
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">买家反馈</h2>
@forelse($feedback as $f)
<p>{{ $f->rating }}★ — {{ \Illuminate\Support\Str::limit($f->comments, 60) }}</p>
@empty
<p style="color:#94a3b8">暂无反馈</p>
@endforelse
</div>
</div>
@endsection
