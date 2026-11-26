<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 库存（FBA / FBM） */
class Inventory extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'listing_id',
        'product_id',
        'marketplace_id',
        'sku',
        'asin',
        'fulfillment_channel',
        'quantity_available',
        'quantity_reserved',
        'quantity_inbound',
        'quantity_unfulfillable',
        'warehouse_location',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function amazonAccount(): BelongsTo
    {
        return $this->belongsTo(AmazonAccount::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function getTotalSellableAttribute(): int
    {
        return (int) $this->quantity_available;
    }
}
