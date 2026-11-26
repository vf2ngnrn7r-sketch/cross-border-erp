<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 广告组 */
class AdGroup extends Model
{
    protected $fillable = [
        'ad_campaign_id',
        'external_ad_group_id',
        'name',
        'state',
        'default_bid',
    ];

    protected function casts(): array
    {
        return [
            'default_bid' => 'decimal:2',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}
