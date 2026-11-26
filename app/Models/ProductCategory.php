<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Browse Node / 类目字典 */
class ProductCategory extends Model
{
    protected $fillable = [
        'organization_id',
        'browse_node_id',
        'name',
        'path',
        'amazon_product_type',
        'attribute_schema',
    ];

    protected function casts(): array
    {
        return [
            'attribute_schema' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
