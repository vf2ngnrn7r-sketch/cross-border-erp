<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $org = $request->user()->resolveOrganization();
        $listings = Listing::query()
            ->with(['amazonAccount', 'marketplace', 'product'])
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('seller_sku', 'like', '%'.$request->q.'%')
                    ->orWhere('asin', 'like', '%'.$request->q.'%')
                    ->orWhere('title', 'like', '%'.$request->q.'%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.listings.index', compact('listings', 'org'));
    }

    public function show(Request $request, Listing $listing): View
    {
        $org = $request->user()->resolveOrganization();
        abort_unless($org && $org->id === $listing->organization_id, 403);
        $listing->load(['product.images', 'marketplace', 'amazonAccount', 'inventories']);

        return view('admin.listings.show', compact('listing'));
    }
}
