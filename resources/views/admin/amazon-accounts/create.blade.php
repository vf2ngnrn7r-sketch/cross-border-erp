@extends('admin.layouts.app')
@section('title', '绑定亚马逊账号')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">绑定亚马逊账号</h1>
<div class="erp-card" style="max-width:640px">
<form method="POST" action="{{ route('admin.amazon-accounts.store') }}">@csrf
<div class="erp-form-row"><label class="erp-label">店铺名称</label><input class="erp-input" name="name" required></div>
<div class="erp-form-row"><label class="erp-label">Seller ID</label><input class="erp-input" name="seller_id" placeholder="A1XXXXXXXX"></div>
<div class="erp-form-row"><label class="erp-label">API 区域</label>
<select class="erp-input" name="api_region"><option value="na">NA</option><option value="eu">EU</option><option value="fe">FE</option></select></div>
<div class="erp-form-row"><label class="erp-label">LWA Client ID（占位即可）</label><input class="erp-input" name="lwa_client_id" placeholder="amzn1.application-oa2-client...."></div>
<div class="erp-form-row"><label class="erp-label">站点</label>
@foreach($marketplaces as $mp)
<label style="display:block;margin:.25rem 0"><input type="checkbox" name="marketplace_ids[]" value="{{ $mp->id }}"> {{ $mp->name }} ({{ $mp->country_code }})</label>
@endforeach
</div>
<p style="color:#94a3b8;font-size:.85rem;margin-bottom:1rem">密钥字段将写入占位符，请勿提交真实密钥到仓库。生产环境使用 .env / 密钥托管。</p>
<button class="erp-btn" type="submit">保存</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.amazon-accounts.index') }}">取消</a>
</form>
</div>
@endsection

