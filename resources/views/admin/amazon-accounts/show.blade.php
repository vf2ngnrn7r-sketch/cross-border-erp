@extends('admin.layouts.app')
@section('title', $amazonAccount->name)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">{{ $amazonAccount->name }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">Seller ID: {{ $amazonAccount->seller_id ?? '—' }} · 凭证状态: {{ $amazonAccount->credential?->status }} · 区域: {{ $amazonAccount->credential?->api_region }}</p>
<form method="POST" action="{{ route('admin.amazon-accounts.sync', $amazonAccount) }}" style="margin-bottom:1rem">@csrf
<input type="hidden" name="scope" value="all">
<button class="erp-btn" type="submit">立即全量同步（队列）</button>
</form>
<div class="erp-card" style="margin-bottom:1rem">
<h2 style="margin-bottom:.75rem">关联站点</h2>
@forelse($amazonAccount->marketplaces as $mp)
<span class="erp-badge" style="margin-right:.35rem">{{ $mp->name }}</span>
@empty
<span style="color:#94a3b8">未绑定站点</span>
@endforelse
</div>
<div class="erp-card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
<h2 style="margin:0">店铺资质</h2>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.index', ['account' => $amazonAccount->id]) }}">查看全部</a>
</div>
<table class="erp-table">
<thead><tr><th>名称</th><th>类型</th><th>状态</th><th>到期</th><th></th></tr></thead>
<tbody>
@forelse($amazonAccount->storeQualifications as $sq)
<tr>
<td>{{ $sq->name }}</td>
<td>{{ $sq->qualification_type }}</td>
<td>{{ $sq->status }}</td>
<td>{{ optional($sq->expires_at)->format('Y-m-d') ?? '—' }}</td>
<td><a style="color:#7dd3fc" href="{{ route('admin.store-qualifications.show', $sq) }}">详情</a></td>
</tr>
@empty
<tr><td colspan="5" style="color:#94a3b8">暂无店铺资质（全量同步会写入 fixture）</td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
