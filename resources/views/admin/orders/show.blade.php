@extends('admin.layouts.app')
@section('title', '订单详情')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">订单 {{ $order->amazon_order_id }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">状态 {{ $order->order_status }} · {{ $order->fulfillment_channel }} · {{ $order->currency }} {{ $order->order_total }}</p>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">订单行</h2>
<table class="erp-table"><thead><tr><th>SKU</th><th>ASIN</th><th>标题</th><th>数量</th><th>已发</th><th>价格</th></tr></thead>
<tbody>
@foreach($order->items as $item)
<tr><td>{{ $item->seller_sku }}</td><td>{{ $item->asin }}</td><td>{{ $item->title }}</td>
<td>{{ $item->quantity_ordered }}</td><td>{{ $item->quantity_shipped }}</td>
<td>{{ $item->currency }} {{ $item->item_price }}</td></tr>
@endforeach
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">履约记录</h2>
@forelse($order->fulfillments as $f)
<p>{{ $f->carrier }} · {{ $f->tracking_number }} · <span class="erp-badge">{{ $f->status }}</span></p>
@empty
<p style="color:#94a3b8">暂无履约记录</p>
@endforelse
</div>
@endsection
