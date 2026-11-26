<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AToZClaim;
use App\Models\CustomerFeedback;
use App\Models\ProductReturn;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $returns = ProductReturn::query()
            ->with(['amazonAccount', 'order'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('return_date')
            ->paginate(20);

        $claims = AToZClaim::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('opened_at')
            ->limit(10)
            ->get();

        $feedback = CustomerFeedback::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('feedback_date')
            ->limit(10)
            ->get();

        return view('admin.returns.index', compact('returns', 'claims', 'feedback', 'org'));
    }
}
