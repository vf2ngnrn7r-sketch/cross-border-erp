@extends('admin.layouts.app')
@section('title', 'Listing')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">站点 Listing</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:280px" name="q" value="{{ request('q') }}" placeholder="SKU / ASIN">
<button class="erp-btn" type="submit">搜索</button>
</form>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>SKU</th><th>ASIN</th><th>标题</th><th>价格</th><th>渠道</th><th>站点</th><th>状态</th></tr></thead>
<tbody>
@forelse($listings as $row)
<tr>
<td>{{ $row->seller_sku }}</td><td>{{ $row->asin }}</td><td>{{ \Illuminate\Support\Str::limit($row->title,40) }}</td>
<td>{{ $row->currency }} {{ $row->price }}</td>
<td><span class="erp-badge">{{ $row->fulfillment_channel }}</span></td>
<td>{{ $row->marketplace?->country_code }}</td>
<td>{{ $row->status }}</td>
</tr>
@empty
<tr><td colspan="7" style="color:#94a3b8">暂无 Listing</td></tr>
@endforelse
</tbody></table>{{ $listings->links() }}</div>
@endsection
