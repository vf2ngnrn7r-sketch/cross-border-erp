<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncJobController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $jobs = SyncJob::query()
            ->with('amazonAccount')
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.sync-jobs.index', compact('jobs', 'org'));
    }

    public function show(Request $request, SyncJob $syncJob): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $syncJob->organization_id, 403);
        $syncJob->load(['amazonAccount', 'reportRequests']);

        return view('admin.sync-jobs.show', compact('syncJob'));
    }
}
