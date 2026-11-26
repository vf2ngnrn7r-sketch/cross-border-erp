@extends('admin.layouts.app')
@section('title', '编辑店铺资质')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">编辑店铺资质</h1>
<form method="POST" action="{{ route('admin.store-qualifications.update', $item) }}" class="erp-card" style="max-width:720px">@csrf @method('PUT')
@include('admin.store-qualifications._form', ['item' => $item])
<button class="erp-btn" type="submit">更新</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.show', $item) }}">取消</a>
</form>
<form method="POST" action="{{ route('admin.store-qualifications.destroy', $item) }}" style="margin-top:1rem" onsubmit="return confirm('确认删除？')">@csrf @method('DELETE')
<button class="erp-btn" type="submit" style="background:#b91c1c">删除</button>
</form>
@endsection
