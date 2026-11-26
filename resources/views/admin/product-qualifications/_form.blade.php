@php($item = $item ?? null)
@php($types = ['certificate'=>'证书','category_eligibility'=>'类目准入','safety_test'=>'安全检测','ce'=>'CE','fcc'=>'FCC','fda'=>'FDA','other'=>'其他'])
@php($statuses = ['valid'=>'有效','expiring'=>'即将过期','expired'=>'已过期','pending'=>'审核中','rejected'=>'驳回'])
<div class="erp-form-row"><label class="erp-label">关联商品</label>
<select class="erp-input" name="product_id">
<option value="">—</option>
@foreach($products as $p)
<option value="{{ $p->id }}" @selected(old('product_id', $item?->product_id)==$p->id)>{{ $p->sku }} / {{ $p->asin }} — {{ \Illuminate\Support\Str::limit($p->title, 40) }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">ASIN / SKU</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="asin" placeholder="ASIN" value="{{ old('asin', $item?->asin) }}">
<input class="erp-input" name="sku" placeholder="SKU" value="{{ old('sku', $item?->sku) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">资质类型 *</label>
<select class="erp-input" name="qualification_type" required>
@foreach($types as $k=>$v)
<option value="{{ $k }}" @selected(old('qualification_type', $item?->qualification_type ?? 'certificate')===$k)>{{ $v }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">名称 *</label><input class="erp-input" name="name" value="{{ old('name', $item?->name) }}" required></div>
<div class="erp-form-row"><label class="erp-label">证件号 / 颁发方</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="document_number" placeholder="证件号" value="{{ old('document_number', $item?->document_number) }}">
<input class="erp-input" name="issuer" placeholder="颁发方" value="{{ old('issuer', $item?->issuer) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">状态 *</label>
<select class="erp-input" name="status" required>
@foreach($statuses as $k=>$v)
<option value="{{ $k }}" @selected(old('status', $item?->status ?? 'valid')===$k)>{{ $v }}</option>
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
