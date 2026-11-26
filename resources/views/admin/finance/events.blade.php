@extends('admin.layouts.app')
@section('title', '费用事件')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">财务费用事件</h1>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>店铺</th><th>类型</th><th>费用项</th><th>金额</th><th>过账日</th></tr></thead>
<tbody>
@forelse($events as $e)
<tr>
<td>{{ $e->amazonAccount?->name }}</td><td>{{ $e->event_type }}</td><td>{{ $e->fee_type }}</td>
<td>{{ $e->currency }} {{ $e->amount }}</td>
<td>{{ optional($e->posted_date)->format('Y-m-d') }}</td>
</tr>
@empty
<tr><td colspan="5" style="color:#94a3b8">暂无事件</td></tr>
@endforelse
</tbody></table>{{ $events->links() }}</div>
@endsection
