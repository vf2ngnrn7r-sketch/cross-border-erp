@extends('admin.layouts.app')
@section('title', $campaign->name)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">{{ $campaign->name }}</h1>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">广告组</h2>
<table class="erp-table">
<thead><tr><th>广告组</th><th>状态</th><th>默认出价</th></tr></thead>
<tbody>
@forelse($campaign->adGroups as $g)
<tr><td>{{ $g->name }}</td><td>{{ $g->state }}</td><td>{{ $g->default_bid }}</td></tr>
@empty
<tr><td colspan="3" style="color:#94a3b8">无广告组</td></tr>
@endforelse
</tbody></table>
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">效果趋势</h2>
<table class="erp-table">
<thead><tr><th>日期</th><th>展示</th><th>点击</th><th>花费</th><th>销售</th><th>订单</th><th>ACoS</th><th>ROAS</th></tr></thead>
<tbody>
@forelse($metrics as $m)
<tr>
<td>{{ optional($m->report_date)->format('Y-m-d') }}</td>
<td>{{ $m->impressions }}</td><td>{{ $m->clicks }}</td>
<td>{{ $m->cost }}</td><td>{{ $m->sales }}</td><td>{{ $m->orders }}</td>
<td>{{ $m->acos !== null ? number_format($m->acos * 100, 1).'%' : '—' }}</td>
<td>{{ $m->roas !== null ? number_format($m->roas, 2) : '—' }}</td>
</tr>
@empty
<tr><td colspan="8" style="color:#94a3b8">暂无日指标</td></tr>
@endforelse
</tbody></table>
</div>
@endsection
