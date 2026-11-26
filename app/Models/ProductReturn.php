<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 退货记录
 * 使用 ProductReturn 避免与 PHP 保留字 return 冲突
 */
class ProductReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'order_id',
        'return_id',
        'asin',
        'seller_sku',
        'reason',
        'status',
        'quantity',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
