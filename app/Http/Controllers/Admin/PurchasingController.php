<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchasingController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $suppliers = Supplier::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->withCount('purchaseOrders')
            ->orderBy('name')
            ->get();

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'items'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->latest()
            ->paginate(20);

        return view('admin.purchasing.index', compact('org', 'suppliers', 'orders'));
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $purchaseOrder->organization_id, 403);
        $purchaseOrder->load(['supplier', 'items', 'shipments']);

        return view('admin.purchasing.show', compact('purchaseOrder'));
    }
}
