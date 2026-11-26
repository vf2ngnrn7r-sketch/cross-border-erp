<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 订单行项目 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'asin',
        'seller_sku',
        'title',
        'quantity_ordered',
        'quantity_shipped',
        'item_price',
        'shipping_price',
        'item_tax',
        'promotion_discount',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'item_price' => 'decimal:2',
            'shipping_price' => 'decimal:2',
            'item_tax' => 'decimal:2',
            'promotion_discount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
