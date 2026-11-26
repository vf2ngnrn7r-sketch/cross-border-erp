<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 货件（FBA 入库 / FBM 出库 / 调拨） */
class Shipment extends Model
{
    protected $fillable = [
        'organization_id', 'amazon_account_id', 'warehouse_id', 'purchase_order_id', 'order_id',
        'shipment_type', 'external_shipment_id', 'reference', 'status', 'carrier',
        'tracking_number', 'destination_fulfillment_center', 'shipped_at',
        'expected_arrival', 'delivered_at', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'expected_arrival' => 'datetime',
            'delivered_at' => 'datetime',
            'raw' => 'array',
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
