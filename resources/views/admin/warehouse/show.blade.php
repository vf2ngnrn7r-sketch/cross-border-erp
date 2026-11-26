@extends('admin.layouts.app')
@section('title', '货件详情')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">{{ $shipment->external_shipment_id ?? '货件 #'.$shipment->id }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">{{ $shipment->shipment_type }} · <span class="erp-badge">{{ $shipment->status }}</span> · {{ $shipment->carrier }} {{ $shipment->tracking_number }}</p>
<div class="erp-card">
<table class="erp-table"><thead><tr><th>SKU</th><th>ASIN</th><th>FNSKU</th><th>发运数</th><th>已收</th></tr></thead>
<tbody>
@foreach($shipment->items as $item)
<tr><td>{{ $item->sku }}</td><td>{{ $item->asin }}</td><td>{{ $item->fnsku }}</td><td>{{ $item->quantity }}</td><td>{{ $item->quantity_received }}</td></tr>
@endforeach
</tbody></table>
</div>
@endsection
