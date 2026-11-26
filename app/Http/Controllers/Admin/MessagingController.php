<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuyerMessage;
use App\Models\SupportCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessagingController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();

        $messages = BuyerMessage::query()
            ->with(['amazonAccount', 'order'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('sent_at')
            ->paginate(20, ['*'], 'messages_page');

        $cases = SupportCase::query()
            ->with('amazonAccount')
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest('opened_at')
            ->paginate(15, ['*'], 'cases_page');

        return view('admin.messaging.index', compact('org', 'messages', 'cases'));
    }
}
