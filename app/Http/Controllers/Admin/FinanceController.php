<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvent;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $settlements = Settlement::query()
            ->with('amazonAccount')
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('deposit_date')
            ->paginate(20);

        return view('admin.finance.index', compact('settlements', 'org'));
    }

    public function show(Request $request, Settlement $settlement): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $settlement->organization_id, 403);
        $settlement->load('financialEvents');
        $events = $settlement->financialEvents;

        return view('admin.finance.show', compact('settlement', 'events'));
    }

    public function events(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $events = FinancialEvent::query()
            ->with(['amazonAccount', 'settlement'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('posted_date')
            ->paginate(30);

        return view('admin.finance.events', compact('events', 'org'));
    }
}
