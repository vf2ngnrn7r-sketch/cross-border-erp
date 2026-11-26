@extends('admin.layouts.app')
@section('title', '任务详情')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:.5rem">同步任务 #{{ $syncJob->id }}</h1>
<p style="color:#94a3b8;margin-bottom:1rem">{{ $syncJob->job_type }} · <span class="erp-badge">{{ $syncJob->status }}</span></p>
<div class="erp-card" style="margin-bottom:1rem">
<pre style="white-space:pre-wrap;font-size:.85rem;color:#cbd5e1">{{ json_encode($syncJob->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
@if($syncJob->error_message)
<p style="color:#fca5a5;margin-top:.75rem">错误：{{ $syncJob->error_message }}</p>
@endif
</div>
@if($syncJob->reportRequests->isNotEmpty())
<div class="erp-card">
<h2 style="margin-bottom:.75rem">报表请求</h2>
@foreach($syncJob->reportRequests as $r)
<p>{{ $r->report_type }} · {{ $r->external_report_id }} · {{ $r->processing_status }}</p>
@endforeach
</div>
@endif
@endsection
