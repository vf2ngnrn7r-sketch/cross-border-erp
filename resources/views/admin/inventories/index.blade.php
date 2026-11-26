@extends('admin.layouts.app')
@section('title', '库存')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">库存（FBA / FBM）</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:200px" name="q" value="{{ request('q') }}" placeholder="SKU / ASIN">
<select class="erp-input" style="max-width:140px" name="channel">
<option value="">全部渠道</option>
<option value="FBA" @selected(request('channel')==='FBA')>FBA</option>
<option value="FBM" @selected(request('channel')==='FBM')>FBM</option>
</select>
<button class="erp-btn" type="submit">筛选</button>
</form>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>SKU</th><th>ASIN</th><th>渠道</th><th>可售</th><th>预留</th><th>在途</th><th>不可售</th><th>同步时间</th></tr></thead>
<tbody>
@forelse($inventories as $inv)
<tr>
<td>@if($inv->product)<a style="color:#7dd3fc" href="{{ route('admin.products.show', $inv->product) }}">{{ $inv->sku }}</a>@else{{ $inv->sku }}@endif</td>
<td>{{ $inv->asin }}</td>
<td><span class="erp-badge">{{ $inv->fulfillment_channel }}</span></td>
<td>{{ $inv->quantity_available }}</td><td>{{ $inv->quantity_reserved }}</td>
<td>{{ $inv->quantity_inbound }}</td><td>{{ $inv->quantity_unfulfillable }}</td>
<td>{{ optional($inv->synced_at)->format('Y-m-d H:i') }}</td>
</tr>
@empty
<tr><td colspan="8" style="color:#94a3b8">暂无库存</td></tr>
@endforelse
</tbody></table>{{ $inventories->links() }}</div>
@endsection
