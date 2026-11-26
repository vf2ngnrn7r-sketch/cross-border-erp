@extends('admin.layouts.app')
@section('title', '仓配')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">仓库 / 货件追踪</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<select class="erp-input" style="max-width:180px" name="type">
<option value="">全部类型</option>
<option value="fba_inbound" @selected(request('type')==='fba_inbound')>FBA 入库</option>
<option value="fbm_outbound" @selected(request('type')==='fbm_outbound')>FBM 出库</option>
<option value="transfer" @selected(request('type')==='transfer')>调拨</option>
</select>
<button class="erp-btn" type="submit">筛选</button>
</form>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">仓库</h2>
<table class="erp-table"><thead><tr><th>编码</th><th>名称</th><th>类型</th><th>国家</th><th>状态</th></tr></thead>
<tbody>
@foreach($warehouses as $w)
<tr><td>{{ $w->code }}</td><td>{{ $w->name }}</td><td><span class="erp-badge">{{ $w->type }}</span></td><td>{{ $w->country }}</td><td>{{ $w->status }}</td></tr>
@endforeach
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">货件</h2>
<table class="erp-table"><thead><tr><th>货件号</th><th>类型</th><th>状态</th><th>承运商</th><th>运单</th><th>目的 FC</th><th>发运</th></tr></thead>
<tbody>
@forelse($shipments as $s)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.warehouse.show', $s) }}">{{ $s->external_shipment_id ?? '#'.$s->id }}</a></td>
<td><span class="erp-badge">{{ $s->shipment_type }}</span></td>
<td>{{ $s->status }}</td>
<td>{{ $s->carrier }}</td><td>{{ $s->tracking_number }}</td>
<td>{{ $s->destination_fulfillment_center }}</td>
<td>{{ optional($s->shipped_at)->format('Y-m-d') }}</td>
</tr>
@empty
<tr><td colspan="7" style="color:#94a3b8">暂无货件</td></tr>
@endforelse
</tbody></table>
{{ $shipments->links() }}
</div>
@endsection
