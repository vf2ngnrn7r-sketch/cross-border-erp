<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 站点 Listing（按 marketplace 状态） */
class Listing extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'product_id',
        'marketplace_id',
        'seller_sku',
        'asin',
        'title',
        'price',
        'currency',
        'fulfillment_channel',
        'status',
        'listing_status',
        'suppress_reason',
        'status_flags',
        'main_image_url',
        'quantity',
        'open_date',
        'attributes',
        'amazon_product_type',
        'browse_node_id',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'synced_at' => 'datetime',
            'open_date' => 'datetime',
            'status_flags' => 'array',
            'attributes' => 'array',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /** 从关联库存刷新 quantity 展示字段 */
    public function syncQuantityFromInventory(): void
    {
        $qty = (int) $this->inventories()->sum('quantity_available');
        $this->forceFill(['quantity' => $qty])->save();
    }

    public function isSuppressed(): bool
    {
        return ($this->listing_status ?? $this->status) === 'suppressed'
            || ($this->listing_status ?? '') === 'SUPPRESSED';
    }
}
