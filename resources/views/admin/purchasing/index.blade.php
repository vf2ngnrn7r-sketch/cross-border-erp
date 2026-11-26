@extends('admin.layouts.app')
@section('title', '采购')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.35rem">采购 / 供应商</h1>
<p style="color:#94a3b8;margin-bottom:1rem">内部 ERP 模块（非 Amazon API）</p>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">供应商</h2>
<table class="erp-table"><thead><tr><th>名称</th><th>编码</th><th>国家</th><th>账期</th><th>采购单数</th><th>状态</th></tr></thead>
<tbody>
@forelse($suppliers as $s)
<tr><td>{{ $s->name }}</td><td>{{ $s->code }}</td><td>{{ $s->country }}</td><td>{{ $s->payment_terms }}</td>
<td>{{ $s->purchase_orders_count }}</td><td><span class="erp-badge">{{ $s->status }}</span></td></tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无供应商</td></tr>
@endforelse
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">采购单</h2>
<table class="erp-table"><thead><tr><th>PO 号</th><th>供应商</th><th>状态</th><th>金额</th><th>下单日</th><th>预计到货</th></tr></thead>
<tbody>
@forelse($orders as $po)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.purchasing.show', $po) }}">{{ $po->po_number }}</a></td>
<td>{{ $po->supplier?->name }}</td>
<td><span class="erp-badge">{{ $po->status }}</span></td>
<td>{{ $po->currency }} {{ $po->total_amount }}</td>
<td>{{ optional($po->ordered_at)->format('Y-m-d') }}</td>
<td>{{ optional($po->expected_at)->format('Y-m-d') }}</td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无采购单</td></tr>
@endforelse
</tbody></table>
{{ $orders->links() }}
</div>
@endsection
