@php($item = $item ?? null)
@php($types = ['brand_registry'=>'品牌备案','category_approval'=>'类目批准','seller_identity'=>'身份核验','tax_info'=>'税务资料','business_license'=>'营业执照','ungating'=>'开白','other'=>'其他'])
@php($statuses = ['approved'=>'已批准','pending'=>'审核中','rejected'=>'驳回','expiring'=>'即将过期','expired'=>'已过期'])
<div class="erp-form-row"><label class="erp-label">亚马逊账号 *</label>
<select class="erp-input" name="amazon_account_id" required>
<option value="">—</option>
@foreach($accounts as $a)
<option value="{{ $a->id }}" @selected(old('amazon_account_id', $item?->amazon_account_id)==$a->id)>{{ $a->name }} ({{ $a->seller_id }})</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">资质类型 *</label>
<select class="erp-input" name="qualification_type" required>
@foreach($types as $k=>$v)
<option value="{{ $k }}" @selected(old('qualification_type', $item?->qualification_type ?? 'brand_registry')===$k)>{{ $v }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">名称 *</label><input class="erp-input" name="name" value="{{ old('name', $item?->name) }}" required></div>
<div class="erp-form-row"><label class="erp-label">品牌 / 类目</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="brand_name" placeholder="品牌名" value="{{ old('brand_name', $item?->brand_name) }}">
<input class="erp-input" name="category_name" placeholder="类目名" value="{{ old('category_name', $item?->category_name) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">证件号</label><input class="erp-input" name="document_number" value="{{ old('document_number', $item?->document_number) }}"></div>
<div class="erp-form-row"><label class="erp-label">状态 *</label>
<select class="erp-input" name="status" required>
@foreach($statuses as $k=>$v)
<option value="{{ $k }}" @selected(old('status', $item?->status ?? 'approved')===$k)>{{ $v }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">颁发日 / 到期日</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" type="date" name="issued_at" value="{{ old('issued_at', optional($item?->issued_at)->format('Y-m-d')) }}">
<input class="erp-input" type="date" name="expires_at" value="{{ old('expires_at', optional($item?->expires_at)->format('Y-m-d')) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">站点</label>
<select class="erp-input" name="marketplace_id">
<option value="">—</option>
@foreach($marketplaces as $m)
<option value="{{ $m->id }}" @selected(old('marketplace_id', $item?->marketplace_id)==$m->id)>{{ $m->name }} ({{ $m->code }})</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">文件 URL</label><input class="erp-input" name="file_url" value="{{ old('file_url', $item?->file_url) }}"></div>
<div class="erp-form-row"><label class="erp-label">备注</label><textarea class="erp-input" name="notes" rows="3">{{ old('notes', $item?->notes) }}</textarea></div>
