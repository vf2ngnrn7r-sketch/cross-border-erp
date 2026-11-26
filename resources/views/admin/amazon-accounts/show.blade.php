@extends('admin.layouts.app')
@section('title', $amazonAccount->name)
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">{{ $amazonAccount->name }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">
Seller ID: {{ $amazonAccount->seller_id ?? '—' }}
· 凭证状态: {{ $amazonAccount->credential?->status }}
· 区域: {{ $amazonAccount->credential?->api_region }}
· 上次同步: {{ optional($amazonAccount->last_synced_at)->diffForHumans() ?? '从未' }}
</p>
<form method="POST" action="{{ route('admin.amazon-accounts.sync', $amazonAccount) }}" style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:1rem">@csrf
<select class="erp-input" name="scope" style="max-width:260px">
@foreach([
  'all' => '全量同步',
  'catalog' => '商品目录 catalog',
  'listings' => 'Listing',
  'inventory' => '库存',
  'orders' => '订单',
  'finance' => '财务',
  'ads' => '广告活动',
  'ad_metrics' => '广告效果',
  'returns' => '退货 / A-Z',
  'fba_inbound' => 'FBA 入库',
  'messaging' => '消息 / Case',
  'product_qualifications' => '商品资质',
  'store_qualifications' => '店铺资质',
] as $value => $label)
<option value="{{ $value }}">{{ $label }}</option>
@endforeach
</select>
<button class="erp-btn" type="submit">投递同步（队列）</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.sync-jobs.index') }}">查看同步任务</a>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.amazon-accounts.index') }}">返回列表</a>
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
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;flex-wrap:wrap;gap:.5rem">
<h2 style="margin:0">店铺资质</h2>
<div>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.create', ['amazon_account_id' => $amazonAccount->id]) }}">新建</a>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.index', ['account' => $amazonAccount->id]) }}">查看全部</a>
</div>
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
