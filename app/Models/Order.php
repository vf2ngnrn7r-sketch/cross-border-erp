<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 亚马逊订单 */
class Order extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'marketplace_id',
        'amazon_order_id',
        'purchase_date',
        'order_status',
        'fulfillment_channel',
        'sales_channel',
        'ship_service_level',
        'order_total',
        'currency',
        'buyer_email',
        'shipping_address',
        'payment_method',
        'is_prime',
        'last_update_date',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'datetime',
            'last_update_date' => 'datetime',
            'order_total' => 'decimal:2',
            'shipping_address' => 'array',
            'is_prime' => 'boolean',
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

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function fulfillments(): HasMany
    {
        return $this->hasMany(Fulfillment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }
}
