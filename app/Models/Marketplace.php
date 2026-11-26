<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** 亚马逊站点 / Marketplace */
class Marketplace extends Model
{
    protected $fillable = [
        'code',
        'country_code',
        'name',
        'currency',
        'domain',
        'region',
    ];

    public function amazonAccounts(): BelongsToMany
    {
        return $this->belongsToMany(AmazonAccount::class, 'amazon_account_marketplace')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
