@extends('admin.layouts.app')
@section('title', '经营报表')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.35rem">经营汇总报表</h1>
<p style="color:#94a3b8;margin-bottom:1rem">聚合订单 / 库存 / 财务 / 广告 / 采购仓配 / 客服</p>

<div class="erp-stats">
<div class="erp-card erp-stat"><div class="n">{{ $openPos }}</div><div class="l">进行中采购单</div></div>
<div class="erp-card erp-stat"><div class="n">{{ $openShipments }}</div><div class="l">在途货件</div></div>
<div class="erp-card erp-stat"><div class="n">{{ $unreadMessages }}</div><div class="l">未读消息</div></div>
<div class="erp-card erp-stat"><div class="n">{{ $openCases }}</div><div class="l">未结 Case</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format((float)$financeTotal, 2) }}</div><div class="l">财务事件合计</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format((float)($adTotals->cost ?? 0), 2) }}</div><div class="l">广告花费</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format((float)($adTotals->sales ?? 0), 2) }}</div><div class="l">广告归因销售额</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
<div class="erp-card">
<h2 style="margin-bottom:.75rem">订单状态</h2>
<table class="erp-table"><thead><tr><th>状态</th><th>单数</th><th>金额</th></tr></thead>
<tbody>
@forelse($orderSummary as $row)
<tr><td>{{ $row->order_status }}</td><td>{{ $row->cnt }}</td><td>{{ number_format((float)$row->amount, 2) }}</td></tr>
@empty
<tr><td colspan="3" style="color:#94a3b8">无数据</td></tr>
@endforelse
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">库存渠道</h2>
<table class="erp-table"><thead><tr><th>渠道</th><th>SKU 数</th><th>可售</th><th>在途</th></tr></thead>
<tbody>
@forelse($inventorySummary as $row)
<tr><td>{{ $row->fulfillment_channel }}</td><td>{{ $row->skus }}</td><td>{{ $row->available }}</td><td>{{ $row->inbound }}</td></tr>
@empty
<tr><td colspan="4" style="color:#94a3b8">无数据</td></tr>
@endforelse
</tbody></table>
</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
<div class="erp-card">
<h2 style="margin-bottom:.75rem">财务事件类型</h2>
<table class="erp-table"><thead><tr><th>类型</th><th>金额</th></tr></thead>
<tbody>
@foreach($financeByType as $row)
<tr><td>{{ $row->event_type }}</td><td>{{ number_format((float)$row->amount, 2) }}</td></tr>
@endforeach
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">广告活动汇总</h2>
<table class="erp-table"><thead><tr><th>活动</th><th>展示</th><th>点击</th><th>花费</th><th>销售</th></tr></thead>
<tbody>
@forelse($adByCampaign as $row)
<tr>
<td>{{ $row->campaign?->name ?? $row->external_campaign_id }}</td>
<td>{{ $row->impressions }}</td><td>{{ $row->clicks }}</td>
<td>{{ number_format((float)$row->cost, 2) }}</td>
<td>{{ number_format((float)$row->sales, 2) }}</td>
</tr>
@empty
<tr><td colspan="5" style="color:#94a3b8">无广告指标，请同步 ad_metrics</td></tr>
@endforelse
</tbody></table>
</div>
</div>
@endsection
