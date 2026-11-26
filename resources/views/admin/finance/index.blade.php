@extends('admin.layouts.app')
@section('title', '财务结算')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">结算单</h1>
<p style="margin-bottom:1rem"><a class="erp-btn erp-btn-ghost" href="{{ route('admin.finance.events') }}">查看费用事件</a></p>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>结算 ID</th><th>店铺</th><th>周期</th><th>入账日</th><th>金额</th><th>状态</th></tr></thead>
<tbody>
@forelse($settlements as $s)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.finance.show', $s) }}">{{ $s->settlement_id }}</a></td>
<td>{{ $s->amazonAccount?->name }}</td>
<td>{{ optional($s->settlement_start)->format('Y-m-d') }} ~ {{ optional($s->settlement_end)->format('Y-m-d') }}</td>
<td>{{ optional($s->deposit_date)->format('Y-m-d') }}</td>
<td>{{ $s->currency }} {{ $s->total_amount }}</td>
<td><span class="erp-badge">{{ $s->status }}</span></td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无结算</td></tr>
@endforelse
</tbody></table>{{ $settlements->links() }}</div>
@endsection
