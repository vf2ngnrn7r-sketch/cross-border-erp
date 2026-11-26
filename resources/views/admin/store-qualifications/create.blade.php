@extends('admin.layouts.app')
@section('title', '新建店铺资质')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">新建店铺资质</h1>
<form method="POST" action="{{ route('admin.store-qualifications.store') }}" class="erp-card" style="max-width:720px">@csrf
@include('admin.store-qualifications._form')
<button class="erp-btn" type="submit">保存</button>
<a class="erp-btn erp-btn-ghost" href="{{ route('admin.store-qualifications.index') }}">取消</a>
</form>
@endsection
