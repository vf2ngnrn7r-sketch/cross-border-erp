@extends('admin.layouts.app')
@section('title', 'Listing')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">站点 Listing</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap" method="GET">
<input class="erp-input" style="max-width:240px" name="q" value="{{ request('q') }}" placeholder="SKU / ASIN / 标题">
<select class="erp-input" style="max-width:140px" name="status">
<option value="">全部状态</option>
@foreach(['active','inactive','suppressed'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
@endforeach
</select>
<button class="erp-btn" type="submit">搜索</button>
</form>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>SKU</th><th>ASIN</th><th>标题</th><th>价格</th><th>数量</th><th>渠道</th><th>站点</th><th>状态</th><th>Listing</th></tr></thead>
<tbody>
@forelse($listings as $row)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.listings.show', $row) }}">{{ $row->seller_sku }}</a></td>
<td>{{ $row->asin }}</td>
<td>{{ \Illuminate\Support\Str::limit($row->title,36) }}</td>
<td>{{ $row->currency }} {{ $row->price }}</td>
<td>{{ $row->quantity ?? '—' }}</td>
<td><span class="erp-badge">{{ $row->fulfillment_channel }}</span></td>
<td>{{ $row->marketplace?->country_code }}</td>
<td>{{ $row->status }}</td>
<td>{{ $row->listing_status }}@if($row->suppress_reason) <span style="color:#fca5a5;font-size:.75rem">({{ \Illuminate\Support\Str::limit($row->suppress_reason,28) }})</span>@endif</td>
</tr>
@empty
<tr><td colspan="9" style="color:#94a3b8">暂无 Listing</td></tr>
@endforelse
</tbody></table>{{ $listings->links() }}</div>
@endsection
