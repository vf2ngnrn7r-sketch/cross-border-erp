@extends('admin.layouts.app')
@section('title', '新建商品')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">新建商品</h1>
<div class="erp-card" style="max-width:640px">
<form method="POST" action="{{ route('admin.products.store') }}">@csrf
@include('admin.products._form')
<button class="erp-btn" type="submit">创建</button>
</form>
</div>
@endsection
