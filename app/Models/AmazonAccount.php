<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** 亚马逊卖家账号（店铺） */
class AmazonAccount extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'seller_id',
        'status',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function marketplaces(): BelongsToMany
    {
        return $this->belongsToMany(Marketplace::class, 'amazon_account_marketplace')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function credential(): HasOne
    {
        return $this->hasOne(AmazonCredential::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function adCampaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
    }

    public function storeQualifications(): HasMany
    {
        return $this->hasMany(StoreQualification::class);
    }
}
