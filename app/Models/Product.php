<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 商品主数据（支持 parent/child 变体）
 * product_type: parent|child|standalone
 */
class Product extends Model
{
    protected $fillable = [
        'organization_id',
        'parent_id',
        'product_type',
        'sku',
        'parent_sku',
        'asin',
        'parent_asin',
        'title',
        'brand',
        'category',
        'browse_node_id',
        'browse_node_name',
        'amazon_product_type',
        'description',
        'bullet_points',
        'variation_theme',
        'attributes',
        'main_image_url',
        'default_cost',
        'default_price',
        'default_currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'bullet_points' => 'array',
            'variation_theme' => 'array',
            'default_cost' => 'decimal:4',
            'default_price' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function variants(): HasMany
    {
        return $this->children();
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(ProductQualification::class);
    }

    public function isParent(): bool
    {
        return $this->product_type === 'parent';
    }

    public function isChild(): bool
    {
        return $this->product_type === 'child';
    }

    /** 可售数量汇总（关联库存） */
    public function getAvailableQuantityAttribute(): int
    {
        return (int) $this->inventories()->sum('quantity_available');
    }
}
