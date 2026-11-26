@extends('admin.layouts.app')
@section('title', $campaign->name)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">{{ $campaign->name }}</h1>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>广告组</th><th>状态</th><th>默认出价</th></tr></thead>
<tbody>
@forelse($campaign->adGroups as $g)
<tr><td>{{ $g->name }}</td><td>{{ $g->state }}</td><td>{{ $g->default_bid }}</td></tr>
@empty
<tr><td colspan="3" style="color:#94a3b8">无广告组</td></tr>
@endforelse
</tbody></table></div>
@endsection
