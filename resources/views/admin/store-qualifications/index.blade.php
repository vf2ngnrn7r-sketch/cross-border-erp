@extends('admin.layouts.app')
@section('title', '店铺资质')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">店铺资质</h1>
<p style="color:#94a3b8;margin-bottom:1rem;font-size:.9rem">品牌备案 / 类目批准 / 合规文件 · 即将过期或已过期 {{ $expiringCount }} 条</p>
<form style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem" method="GET">
<input class="erp-input" style="max-width:220px" name="q" value="{{ request('q') }}" placeholder="名称 / 品牌 / 类目 / 证件号">
<select class="erp-input" style="max-width:180px" name="account">
<option value="">账号</option>
@foreach($accounts as $a)
<option value="{{ $a->id }}" @selected(request('account')==$a->id)>{{ $a->name }}</option>
@endforeach
</select>
<select class="erp-input" style="max-width:140px" name="status">
<option value="">状态</option>
@foreach(['approved','pending','rejected','expiring','expired'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
@endforeach
</select>
<select class="erp-input" style="max-width:160px" name="type">
<option value="">类型</option>
@foreach(['brand_registry','category_approval','seller_identity','tax_info','business_license','ungating','other'] as $t)
<option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
@endforeach
</select>
<label style="display:flex;align-items:center;gap:.35rem;color:#94a3b8;font-size:.85rem">
<input type="checkbox" name="expiring" value="1" @checked(request()->boolean('expiring'))> 30 天内到期
</label>
<button class="erp-btn" type="submit">筛选</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.create') }}">新建</a>
</form>
<div class="erp-card">
<table class="erp-table">
<thead><tr><th>名称</th><th>类型</th><th>账号</th><th>品牌 / 类目</th><th>状态</th><th>到期</th><th></th></tr></thead>
<tbody>
@forelse($items as $row)
<tr>
<td>{{ $row->name }}</td>
<td><span class="erp-badge">{{ $row->qualification_type }}</span></td>
<td>{{ $row->amazonAccount?->name }}</td>
<td>{{ $row->brand_name ?? '—' }}@if($row->category_name)<br><span style="color:#94a3b8;font-size:.8rem">{{ $row->category_name }}</span>@endif</td>
<td>@if(in_array($row->status, ['expiring','expired'], true))
<span class="erp-badge" style="background:rgba(248,113,113,.2);color:#fca5a5">{{ $row->status }}</span>
@else<span class="erp-badge">{{ $row->status }}</span>@endif</td>
<td>{{ optional($row->expires_at)->format('Y-m-d') ?? '—' }}</td>
<td>
<a style="color:#7dd3fc" href="{{ route('admin.store-qualifications.show', $row) }}">详情</a>
· <a style="color:#7dd3fc" href="{{ route('admin.store-qualifications.edit', $row) }}">编辑</a>
</td>
</tr>
@empty
<tr><td colspan="7" style="color:#94a3b8">暂无店铺资质</td></tr>
@endforelse
</tbody>
</table>
</div>
<div style="margin-top:1rem">{{ $items->links() }}</div>
@endsection
