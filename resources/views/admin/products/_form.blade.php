@php($product = $product ?? null)
<div class="erp-form-row"><label class="erp-label">SKU *</label><input class="erp-input" name="sku" value="{{ old('sku', $product?->sku) }}" required></div>
<div class="erp-form-row"><label class="erp-label">ASIN</label><input class="erp-input" name="asin" value="{{ old('asin', $product?->asin) }}"></div>
<div class="erp-form-row"><label class="erp-label">标题 *</label><input class="erp-input" name="title" value="{{ old('title', $product?->title) }}" required></div>
<div class="erp-form-row"><label class="erp-label">品牌</label><input class="erp-input" name="brand" value="{{ old('brand', $product?->brand) }}"></div>
<div class="erp-form-row"><label class="erp-label">类型</label>
<select class="erp-input" name="product_type">
@foreach(['standalone'=>'单品','parent'=>'父体','child'=>'子体'] as $k=>$v)
<option value="{{ $k }}" @selected(old('product_type', $product?->product_type ?? 'standalone')===$k)>{{ $v }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">父体商品</label>
<select class="erp-input" name="parent_id">
<option value="">—</option>
@foreach($parents as $par)
<option value="{{ $par->id }}" @selected(old('parent_id', $product?->parent_id)==$par->id)>{{ $par->sku }} — {{ $par->title }}</option>
@endforeach
</select></div>
<div class="erp-form-row"><label class="erp-label">父 SKU / 父 ASIN</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="parent_sku" placeholder="parent_sku" value="{{ old('parent_sku', $product?->parent_sku) }}">
<input class="erp-input" name="parent_asin" placeholder="parent_asin" value="{{ old('parent_asin', $product?->parent_asin) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">类目 / Browse Node</label>
<input class="erp-input" name="category" placeholder="类目名" value="{{ old('category', $product?->category) }}" style="margin-bottom:.35rem">
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="browse_node_id" placeholder="browse_node_id" value="{{ old('browse_node_id', $product?->browse_node_id) }}">
<input class="erp-input" name="browse_node_name" placeholder="browse_node_name" value="{{ old('browse_node_name', $product?->browse_node_name) }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">Amazon Product Type</label><input class="erp-input" name="amazon_product_type" value="{{ old('amazon_product_type', $product?->amazon_product_type) }}"></div>
<div class="erp-form-row"><label class="erp-label">颜色 / 尺码属性</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="attr_color" placeholder="color" value="{{ old('attr_color', $product?->attributes['color'] ?? '') }}">
<input class="erp-input" name="attr_size" placeholder="size" value="{{ old('attr_size', $product?->attributes['size'] ?? '') }}">
</div></div>
<div class="erp-form-row"><label class="erp-label">默认价格 / 成本</label>
<div style="display:flex;gap:.5rem">
<input class="erp-input" name="default_price" type="number" step="0.01" value="{{ old('default_price', $product?->default_price) }}">
<input class="erp-input" name="default_cost" type="number" step="0.0001" value="{{ old('default_cost', $product?->default_cost) }}">
<input class="erp-input" name="default_currency" maxlength="3" value="{{ old('default_currency', $product?->default_currency ?? 'USD') }}" style="max-width:90px">
</div></div>
<div class="erp-form-row"><label class="erp-label">主图 URL</label><input class="erp-input" name="main_image_url" value="{{ old('main_image_url', $product?->main_image_url) }}"></div>
<div class="erp-form-row"><label class="erp-label">描述</label><textarea class="erp-input" name="description" rows="3">{{ old('description', $product?->description) }}</textarea></div>
<div class="erp-form-row"><label class="erp-label">卖点（每行一条）</label>
<textarea class="erp-input" name="bullet_points_text" rows="4">{{ old('bullet_points_text', is_array($product?->bullet_points) ? implode("\n", $product->bullet_points) : '') }}</textarea>
</div>
<div class="erp-form-row"><label class="erp-label">状态</label>
<select class="erp-input" name="status">
@foreach(['draft'=>'草稿','active'=>'在售','archived'=>'归档'] as $k=>$v)
<option value="{{ $k }}" @selected(old('status', $product?->status ?? 'active')===$k)>{{ $v }}</option>
@endforeach
</select></div>
