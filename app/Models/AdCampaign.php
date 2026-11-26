<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 广告活动 */
class AdCampaign extends Model
{
    protected $fillable = [
        'organization_id',
        'amazon_account_id',
        'marketplace_id',
        'external_campaign_id',
        'name',
        'campaign_type',
        'targeting_type',
        'state',
        'daily_budget',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'daily_budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function adGroups(): HasMany
    {
        return $this->hasMany(AdGroup::class);
    }
}
