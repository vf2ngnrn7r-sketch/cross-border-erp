@extends('admin.layouts.app')
@section('title', $purchaseOrder->po_number)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">采购单 {{ $purchaseOrder->po_number }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">供应商 {{ $purchaseOrder->supplier?->name }} · <span class="erp-badge">{{ $purchaseOrder->status }}</span> · {{ $purchaseOrder->currency }} {{ $purchaseOrder->total_amount }}</p>
<div class="erp-card" style="margin-bottom:1rem">
<table class="erp-table"><thead><tr><th>SKU</th><th>标题</th><th>数量</th><th>已收</th><th>单价</th></tr></thead>
<tbody>
@foreach($purchaseOrder->items as $item)
<tr><td>{{ $item->sku }}</td><td>{{ $item->title }}</td><td>{{ $item->quantity }}</td><td>{{ $item->received_quantity }}</td><td>{{ $item->unit_cost }}</td></tr>
@endforeach
</tbody></table>
</div>
@if($purchaseOrder->shipments->isNotEmpty())
<div class="erp-card"><h2 style="margin-bottom:.75rem">关联货件</h2>
@foreach($purchaseOrder->shipments as $ship)
<p><a style="color:#7dd3fc" href="{{ route('admin.warehouse.show', $ship) }}">{{ $ship->external_shipment_id ?? '#'.$ship->id }}</a> · {{ $ship->shipment_type }} · {{ $ship->status }}</p>
@endforeach
</div>
@endif
@endsection
