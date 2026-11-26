<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 客服 / 账号 Case */
class SupportCase extends Model
{
    protected $fillable = [
        'organization_id', 'amazon_account_id', 'order_id', 'case_id', 'case_type',
        'status', 'subject', 'description', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
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
