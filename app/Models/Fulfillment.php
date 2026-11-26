<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 履约 / 发货记录 */
class Fulfillment extends Model
{
    protected $fillable = [
        'order_id',
        'shipment_id',
        'carrier',
        'tracking_number',
        'status',
        'shipped_at',
        'estimated_arrival',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_arrival' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
