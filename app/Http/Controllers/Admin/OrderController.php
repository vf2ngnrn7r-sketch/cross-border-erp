<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $orders = Order::query()
            ->with(['amazonAccount', 'marketplace'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->status, fn ($q) => $q->where('order_status', $request->status))
            ->when($request->q, fn ($q) => $q->where('amazon_order_id', 'like', '%'.$request->q.'%'))
            ->latest('purchase_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'org'));
    }

    public function show(Request $request, Order $order): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $order->organization_id, 403);
        $order->load(['items', 'fulfillments', 'amazonAccount', 'marketplace']);

        return view('admin.orders.show', compact('order'));
    }
}
