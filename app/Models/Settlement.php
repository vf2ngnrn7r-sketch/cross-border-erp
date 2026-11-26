<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 结算单 */
class Settlement extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'settlement_id',
        'settlement_start',
        'settlement_end',
        'deposit_date',
        'total_amount',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settlement_start' => 'datetime',
            'settlement_end' => 'datetime',
            'deposit_date' => 'datetime',
            'total_amount' => 'decimal:2',
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

    public function financialEvents(): HasMany
    {
        return $this->hasMany(FinancialEvent::class);
    }
}
