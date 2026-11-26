@extends('admin.layouts.app')
@section('title', '订单')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">订单</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:240px" name="q" value="{{ request('q') }}" placeholder="Amazon Order ID">
<input class="erp-input" style="max-width:160px" name="status" value="{{ request('status') }}" placeholder="状态">
<button class="erp-btn" type="submit">搜索</button>
</form>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>订单号</th><th>店铺</th><th>状态</th><th>履约</th><th>金额</th><th>下单时间</th></tr></thead>
<tbody>
@forelse($orders as $order)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.orders.show', $order) }}">{{ $order->amazon_order_id }}</a></td>
<td>{{ $order->amazonAccount?->name }}</td>
<td><span class="erp-badge">{{ $order->order_status }}</span></td>
<td>{{ $order->fulfillment_channel }}</td>
<td>{{ $order->currency }} {{ $order->order_total }}</td>
<td>{{ optional($order->purchase_date)->format('Y-m-d H:i') }}</td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无订单</td></tr>
@endforelse
</tbody></table>{{ $orders->links() }}</div>
@endsection
