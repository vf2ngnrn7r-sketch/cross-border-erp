@extends('admin.layouts.app')
@section('title', '结算详情')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">结算 {{ $settlement->settlement_id }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">合计 {{ $settlement->currency }} {{ $settlement->total_amount }} · {{ $settlement->status }}</p>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>类型</th><th>费用项</th><th>金额</th><th>过账日</th></tr></thead>
<tbody>
@foreach($events as $e)
<tr><td>{{ $e->event_type }}</td><td>{{ $e->fee_type }}</td>
<td>{{ $e->currency }} {{ $e->amount }}</td>
<td>{{ optional($e->posted_date)->format('Y-m-d H:i') }}</td></tr>
@endforeach
</tbody></table></div>
@endsection
