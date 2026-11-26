<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 买家反馈 */
class CustomerFeedback extends Model
{
    protected $table = 'customer_feedback';

    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'order_id',
        'rating',
        'comments',
        'feedback_date',
    ];

    protected function casts(): array
    {
        return [
            'feedback_date' => 'datetime',
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
