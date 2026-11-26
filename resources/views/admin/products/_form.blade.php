@php($product = $product ?? null)
<div class="erp-form-row"><label class="erp-label">SKU</label><input class="erp-input" name="sku" value="{{ old('sku', $product?->sku) }}" required></div>
<div class="erp-form-row"><label class="erp-label">ASIN</label><input class="erp-input" name="asin" value="{{ old('asin', $product?->asin) }}"></div>
<div class="erp-form-row"><label class="erp-label">标题</label><input class="erp-input" name="title" value="{{ old('title', $product?->title) }}" required></div>
<div class="erp-form-row"><label class="erp-label">品牌</label><input class="erp-input" name="brand" value="{{ old('brand', $product?->brand) }}"></div>
<div class="erp-form-row"><label class="erp-label">类目</label><input class="erp-input" name="category" value="{{ old('category', $product?->category) }}"></div>
<div class="erp-form-row"><label class="erp-label">状态</label>
<select class="erp-input" name="status">
@foreach(['draft'=>'草稿','active'=>'在售','archived'=>'归档'] as $k=>$v)
<option value="{{ $k }}" @selected(old('status', $product?->status ?? 'active')===$k)>{{ $v }}</option>
@endforeach
</select></div>
