<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $inventories = Inventory::query()
            ->with(['amazonAccount', 'marketplace'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->channel, fn ($q) => $q->where('fulfillment_channel', $request->channel))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('sku', 'like', '%'.$request->q.'%')
                    ->orWhere('asin', 'like', '%'.$request->q.'%');
            }))
            ->latest('synced_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventories.index', compact('inventories', 'org'));
    }
}
