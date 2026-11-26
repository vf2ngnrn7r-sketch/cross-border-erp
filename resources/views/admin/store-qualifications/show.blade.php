@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
<h1 style="font-size:1.4rem;margin:0">{{ $item->name }}</h1>
<div>
<a class="erp-btn" href="{{ route('admin.store-qualifications.edit', $item) }}">编辑</a>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.index') }}">返回</a>
</div>
</div>
<div class="erp-card" style="max-width:800px">
<table class="erp-table">
<tr><th>类型</th><td>{{ $item->qualification_type }}</td></tr>
<tr><th>状态</th><td>{{ $item->status }}</td></tr>
<tr><th>亚马逊账号</th><td>@if($item->amazonAccount)<a style="color:#7dd3fc" href="{{ route('admin.amazon-accounts.show', $item->amazonAccount) }}">{{ $item->amazonAccount->name }}</a>@else—@endif</td></tr>
<tr><th>品牌 / 类目</th><td>{{ $item->brand_name ?? '—' }} / {{ $item->category_name ?? '—' }}</td></tr>
<tr><th>证件号</th><td>{{ $item->document_number ?? '—' }}</td></tr>
<tr><th>颁发 / 到期</th><td>{{ optional($item->issued_at)->format('Y-m-d') ?? '—' }} → {{ optional($item->expires_at)->format('Y-m-d') ?? '长期' }}</td></tr>
<tr><th>站点</th><td>{{ $item->marketplace?->name ?? '—' }}</td></tr>
<tr><th>文件</th><td>@if($item->file_url)<a style="color:#7dd3fc" href="{{ $item->file_url }}" target="_blank" rel="noopener">打开</a>@else—@endif</td></tr>
<tr><th>外部 ID</th><td>{{ $item->external_id ?? '—' }}</td></tr>
<tr><th>备注</th><td>{{ $item->notes ?? '—' }}</td></tr>
</table>
</div>
@endsection
