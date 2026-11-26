@extends('admin.layouts.app')
@section('title', '广告')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">广告活动（桩）</h1>
<div class="erp-card"><table class="erp-table">
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
</tbody></table>{{ $campaigns->links() }}</div>
@endsection
