<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 店铺 / 卖家账号资质（品牌备案、类目批准等） */
class StoreQualification extends Model
{
    protected $fillable = [
        'organization_id', 'amazon_account_id', 'marketplace_id',
        'qualification_type', 'name', 'brand_name', 'category_name',
        'document_number', 'status', 'issued_at', 'expires_at',
        'file_url', 'external_id', 'notes', 'raw',
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

    public function amazonAccount(): BelongsTo
    {
        return $this->belongsTo(AmazonAccount::class);
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
            $this->status = 'approved';
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
