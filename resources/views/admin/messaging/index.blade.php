@extends('admin.layouts.app')
@section('title', '消息与 Case')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">买家消息 / 客服 Case</h1>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">消息</h2>
<table class="erp-table"><thead><tr><th>方向</th><th>订单</th><th>主题</th><th>状态</th><th>时间</th></tr></thead>
<tbody>
@forelse($messages as $m)
<tr>
<td><span class="erp-badge">{{ $m->direction }}</span></td>
<td>{{ $m->amazon_order_id }}</td>
<td>{{ $m->subject }}</td>
<td>{{ $m->status }}</td>
<td>{{ optional($m->sent_at)->format('Y-m-d H:i') }}</td>
</tr>
@empty
<tr><td colspan="5" style="color:#94a3b8">暂无消息</td></tr>
@endforelse
</tbody></table>
{{ $messages->links() }}
</div>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">Cases</h2>
<table class="erp-table"><thead><tr><th>Case ID</th><th>类型</th><th>主题</th><th>状态</th><th>打开</th></tr></thead>
<tbody>
@forelse($cases as $c)
<tr>
<td>{{ $c->case_id }}</td><td>{{ $c->case_type }}</td><td>{{ $c->subject }}</td>
<td><span class="erp-badge">{{ $c->status }}</span></td>
<td>{{ optional($c->opened_at)->format('Y-m-d') }}</td>
</tr>
@empty
<tr><td colspan="5" style="color:#94a3b8">暂无 Case</td></tr>
@endforelse
</tbody></table>
{{ $cases->links() }}
</div>
@endsection
