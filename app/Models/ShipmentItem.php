<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 货件明细 */
class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id', 'sku', 'asin', 'fnsku', 'quantity', 'quantity_received',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
