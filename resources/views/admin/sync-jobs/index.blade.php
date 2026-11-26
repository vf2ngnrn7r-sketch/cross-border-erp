@extends('admin.layouts.app')
@section('title', '同步任务')
@section('content')
<h1 style="font-size:1.4rem;margin-bottom:1rem">同步 / 报表任务</h1>
<form style="display:flex;gap:.5rem;margin-bottom:1rem" method="GET">
<select class="erp-input" style="max-width:160px" name="status">
<option value="">全部状态</option>
@foreach(['pending','running','succeeded','failed'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
@endforeach
</select>
<button class="erp-btn" type="submit">筛选</button>
</form>
<div class="erp-card"><table class="erp-table">
<thead><tr><th>ID</th><th>类型</th><th>报表</th><th>店铺</th><th>状态</th><th>开始</th><th>结束</th></tr></thead>
<tbody>
@forelse($jobs as $job)
<tr>
<td><a style="color:#7dd3fc" href="{{ route('admin.sync-jobs.show', $job) }}">#{{ $job->id }}</a></td>
<td>{{ $job->job_type }}</td><td>{{ $job->report_type ?? '—' }}</td>
<td>{{ $job->amazonAccount?->name }}</td>
<td><span class="erp-badge">{{ $job->status }}</span></td>
<td>{{ optional($job->started_at)->format('m-d H:i') }}</td>
<td>{{ optional($job->finished_at)->format('m-d H:i') }}</td>
</tr>
@empty
<tr><td colspan="7" style="color:#94a3b8">暂无任务</td></tr>
@endforelse
</tbody></table>{{ $jobs->links() }}</div>
@endsection
