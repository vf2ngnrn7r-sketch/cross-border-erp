@extends('admin.layouts.app')
@section('title', $amazonAccount->name)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">{{ $amazonAccount->name }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">Seller ID: {{ $amazonAccount->seller_id ?? '—' }} · 凭证状态: {{ $amazonAccount->credential?->status }} · 区域: {{ $amazonAccount->credential?->api_region }}</p>
<form method="POST" action="{{ route('admin.amazon-accounts.sync', $amazonAccount) }}" style="margin-bottom:1rem">@csrf
<input type="hidden" name="scope" value="all">
<button class="erp-btn" type="submit">立即全量同步（队列）</button>
</form>
<div class="erp-card">
<h2 style="margin-bottom:.75rem">关联站点</h2>
@forelse($amazonAccount->marketplaces as $mp)
<span class="erp-badge" style="margin-right:.35rem">{{ $mp->name }}</span>
@empty
<span style="color:#94a3b8">未绑定站点</span>
@endforelse
</div>
@endsection
