<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** 商品主数据 */
class Product extends Model
{
    protected $fillable = [
        'organization_id',
        'sku',
        'asin',
        'title',
        'brand',
        'category',
        'attributes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
