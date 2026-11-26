<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $warehouses = Warehouse::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->orderBy('name')
            ->get();

        $shipments = Shipment::query()
            ->with(['warehouse', 'amazonAccount', 'items'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->type, fn ($q) => $q->where('shipment_type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.warehouse.index', compact('org', 'warehouses', 'shipments'));
    }

    public function show(Request $request, Shipment $shipment): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $shipment->organization_id, 403);
        $shipment->load(['items', 'warehouse', 'amazonAccount', 'purchaseOrder', 'order']);

        return view('admin.warehouse.show', compact('shipment'));
    }
}
