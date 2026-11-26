@extends('admin.layouts.app')
@section('title', '亚马逊账号')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">亚马逊账号 / SP-API 绑定</h1>
<p style="margin-bottom:1rem"><a class="erp-btn" href="{{ route('admin.amazon-accounts.create') }}">绑定新账号</a></p>
<div class="erp-card">
<table class="erp-table">
<thead><tr><th>名称</th><th>Seller ID</th><th>站点</th><th>凭证</th><th>上次同步</th><th>操作</th></tr></thead>
<tbody>
@forelse($accounts as $account)
<tr>
<td><a href="{{ route('admin.amazon-accounts.show', $account) }}" style="color:#7dd3fc">{{ $account->name }}</a></td>
<td>{{ $account->seller_id ?? '—' }}</td>
<td>{{ $account->marketplaces->pluck('country_code')->join(', ') }}</td>
<td><span class="erp-badge">{{ $account->credential?->status ?? '无' }}</span></td>
<td>{{ optional($account->last_synced_at)->diffForHumans() ?? '从未' }}</td>
<td>
<form method="POST" action="{{ route('admin.amazon-accounts.sync', $account) }}" style="display:inline">@csrf
<button class="erp-btn erp-btn-ghost" type="submit">同步</button></form>
</td>
</tr>
@empty
<tr><td colspan="6" style="color:#94a3b8">暂无账号</td></tr>
@endforelse
</tbody>
</table>
{{ $accounts->links() }}
</div>
@endsection
