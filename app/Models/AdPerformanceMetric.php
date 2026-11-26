<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 广告日维度效果指标 */
class AdPerformanceMetric extends Model
{
    protected $fillable = [
        'organization_id', 'amazon_account_id', 'ad_campaign_id', 'external_campaign_id',
        'report_date', 'impressions', 'clicks', 'cost', 'sales', 'orders',
        'acos', 'roas', 'currency',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'cost' => 'decimal:2',
            'sales' => 'decimal:2',
            'acos' => 'decimal:4',
            'roas' => 'decimal:4',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
