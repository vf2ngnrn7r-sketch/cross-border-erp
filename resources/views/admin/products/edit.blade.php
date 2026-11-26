@extends('admin.layouts.app')
@section('title', '编辑商品')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">编辑商品</h1>
<div class="erp-card" style="max-width:720px">
<form method="POST" action="{{ route('admin.products.update', $product) }}">@csrf @method('PUT')
@include('admin.products._form', ['product' => $product])
<button class="erp-btn" type="submit">保存</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.products.show', $product) }}">取消</a>
</form>
<form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="margin-top:1rem" onsubmit="return confirm('确认删除？')">@csrf @method('DELETE')
<button class="erp-btn" type="submit" style="background:#b91c1c">删除</button>
</form>
</div>
@endsection
