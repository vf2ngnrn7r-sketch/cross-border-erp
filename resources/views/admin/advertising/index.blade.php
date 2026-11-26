@extends('admin.layouts.app')
@section('title', '广告')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">广告活动与效果</h1>
<div class="erp-stats">
<div class="erp-card erp-stat"><div class="n">{{ number_format($totals['impressions']) }}</div><div class="l">展示</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format($totals['clicks']) }}</div><div class="l">点击</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format($totals['cost'], 2) }}</div><div class="l">花费</div></div>
<div class="erp-card erp-stat"><div class="n">{{ number_format($totals['sales'], 2) }}</div><div class="l">归因销售</div></div>
<div class="erp-card erp-stat"><div class="n">{{ $totals['orders'] }}</div><div class="l">归因订单</div></div>
</div>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">活动列表</h2>
<table class="erp-table">
<thead><tr><th>名称</th><th>类型</th><th>定向</th><th>日预算</th><th>状态</th><th>广告组</th></tr></thead>
<tbody>
@forelse($campaigns as $c)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.advertising.show', $c) }}">{{ $c->name }}</a></td>
<td>{{ $c->campaign_type }}</td><td>{{ $c->targeting_type }}</td>
<td>{{ $c->daily_budget }}</td>
<td><span class="erp-badge">{{ $c->state }}</span></td>
<td>{{ $c->adGroups->count() }}</td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无广告活动</td></tr>
@endforelse
</tbody></table>{{ $campaigns->links() }}
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">日效果指标（Fixture）</h2>
<table class="erp-table">
<thead><tr><th>日期</th><th>活动</th><th>展示</th><th>点击</th><th>花费</th><th>销售</th><th>ACoS</th><th>ROAS</th></tr></thead>
<tbody>
@forelse($metrics as $m)
<tr>
<td>{{ optional($m->report_date)->format('Y-m-d') }}</td>
<td>{{ $m->campaign?->name ?? $m->external_campaign_id }}</td>
<td>{{ $m->impressions }}</td><td>{{ $m->clicks }}</td>
<td>{{ $m->cost }}</td><td>{{ $m->sales }}</td>
<td>{{ $m->acos !== null ? number_format($m->acos * 100, 1).'%' : '—' }}</td>
<td>{{ $m->roas !== null ? number_format($m->roas, 2) : '—' }}</td>
</tr>
@empty
<tr><td colspan="8" style="color:#94a3b8">暂无指标，执行 php artisan amazon:sync --scope=ad_metrics</td></tr>
@endforelse
</tbody></table>
</div>
@endsection
