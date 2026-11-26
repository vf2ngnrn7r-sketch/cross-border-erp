<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 商品资质 / 证书 / 类目准入文件 */
class ProductQualification extends Model
{
    protected $fillable = [
        'organization_id', 'product_id', 'marketplace_id', 'asin', 'sku',
        'qualification_type', 'name', 'document_number', 'issuer', 'status',
        'issued_at', 'expires_at', 'file_url', 'external_id', 'notes', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'raw' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function refreshExpiryStatus(): void
    {
        if (! $this->expires_at) {
            return;
        }
        if ($this->expires_at->isPast()) {
            $this->status = 'expired';
        } elseif ($this->expires_at->lte(now()->addDays(30))) {
            $this->status = 'expiring';
        } elseif (in_array($this->status, ['expired', 'expiring'], true)) {
            $this->status = 'valid';
        }
        $this->save();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->lte(now()->addDays($days));
    }
}
